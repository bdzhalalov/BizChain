<?php

namespace App\Http\Services;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderService
{
    protected CategoryService $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    public function purchaseProducts(array $data)
    {
        Log::debug(
            "Start purchase products from provider",
            [
                "provider_id" => $data['provider_id'],
            ]
        );
        try {
            DB::beginTransaction();

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
            DB::table('batch_products')->insert($batchProducts);

            // linking products with storage
            // get existing products in the storage
            $existingProducts = DB::table('storage_products')->
                where('storage_id', $data['storage_id'])->
                whereIn('product_id', array_keys($productQuantities))->
                get()->
                keyBy('product_id');

            // update quantity for existing products in storage or add new product to storage
            $storageProducts = [];
            foreach ($productQuantities as $productId => $quantity) {
                if (isset($existingProducts[$productId])) {
                    DB::table('storage_products')
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
                    ];
                }
            }

            if (!empty($storageProducts)) {
                DB::table('storage_products')->insert($storageProducts);
            }

            $this->makePayment();

            DB::commit();
        } catch (\Throwable $exception) {
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
