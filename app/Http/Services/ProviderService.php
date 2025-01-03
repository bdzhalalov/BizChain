<?php

namespace App\Http\Services;

use App\Exceptions\NotFoundException;
use App\Http\Resources\ProviderResource;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProviderService
{
    protected CategoryService $categoryService;
    protected StorageService $storageService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
        $this->storageService = new StorageService();
    }

    /**
     * @return Collection
     */
    public function getListOfProviders(): Collection
    {
        Log::debug("Start getting list of providers");

        $response = Provider::all();

        return ProviderResource::collection($response);
    }

    /**
     * @param int $providerId
     * @return ProviderResource
     * @throws NotFoundException
     */
    public function getProviderById(int $providerId): ProviderResource
    {
        Log::debug("Start getting provider by id");

        $provider = Provider::where('id', $providerId)->first();

        if (empty($provider)) {
            throw NotFoundException::getInstance("Provider with id $providerId not found");
        }

        return new ProviderResource($provider);
    }

    /**
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function purchaseProducts(array $data): void
    {
        Log::debug(
            "Start purchase products from provider",
            [
                "provider_id" => $data['provider_id'],
            ]
        );
        try {
            DB::beginTransaction();

            //check provider existence
            $this->getProviderById($data['provider_id']);

            //check storage existence
            $this->storageService->getStorageById($data['storage_id']);

            //Creating new batch
            $batch = Batch::create(['provider_id' => $data['provider_id']]);

            // Find category id for each product or create new category if it doesn't exist
            $productsCategoriesIds = $this->categoryService->getProductsCategories($data['products']);

            $preparedProducts = [];
            $additionalProductInfo = [];
            foreach ($data['products'] as $product) {
                // logic for increasing product's purchase price for profit
                $price = $this->increaseProductPrice($product['purchase_price']);

                $preparedProducts[] = [
                    'name' => $product['name'],
                    'category_id' => $productsCategoriesIds[$product['category']],
                    'price' => $price,
                ];

                $additionalProductInfo[$product['name']] = [
                    'quantity' => $product['quantity'],
                    'purchase_price' => $product['purchase_price'],
                ];
            }

            // insert new products to db
            Product::upsert($preparedProducts, ['name', 'category_id'], []);
            $createdProducts = Product::whereIn('name', collect($preparedProducts)->pluck('name'))->get();

            // linking products with batch
            $batchProducts = [];
            $productQuantities = [];
            foreach ($createdProducts as $product) {
                $batchProducts[] = [
                    'batch_id' => $batch->id,
                    'product_id' => $product['id'],
                    'quantity' => $additionalProductInfo[$product['name']]['quantity'],
                    'purchase_price' => $additionalProductInfo[$product['name']]['purchase_price'],
                ];

                if (!isset($productQuantities[$product['id']])) {
                    $productQuantities[$product['id']] = 0;
                }
                $productQuantities[$product['id']] += $additionalProductInfo[$product['name']]['quantity'];
            }
            DB::table('batch_product')->insert($batchProducts);

            // linking products with storage
            // get existing products in the storage
            $existingProducts = DB::table('product_storage')->
                where('storage_id', $data['storage_id'])->
                whereIn('product_id', array_keys($productQuantities))->
                get()->
                keyBy('product_id');

            // update quantity for existing products in storage or add new product to storage
            $storageProducts = [];
            foreach ($productQuantities as $productId => $quantity) {
                if (isset($existingProducts[$productId])) {
                    DB::table('product_storage')
                        ->where('storage_id', $data['storage_id'])
                        ->where('product_id', $productId)
                        ->update([
                            'quantity' => $existingProducts[$productId]->quantity + $quantity,
                            'updated_at' => now(),
                        ]);
                } else {
                    $storageProducts[] = [
                        'storage_id' => $data['storage_id'],
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($storageProducts)) {
                DB::table('product_storage')->insert($storageProducts);
            }

            $this->makePayment();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param float $price
     * @return float
     */
    private function increaseProductPrice(float $price): float
    {
        // for simple example the price will be increased by 10%
        return $price + round($price * 0.1);
    }

    private function makePayment(): void
    {
        // logic of making payment to the provider for purchased products
    }
}
