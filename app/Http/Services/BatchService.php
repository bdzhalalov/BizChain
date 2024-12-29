<?php

namespace App\Http\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchService
{
    protected ProviderService $providerService;
    protected StorageService $storageService;

    public function __construct()
    {
        $this->providerService = new ProviderService();
        $this->storageService = new StorageService();
    }

    /**
     * @throws NotFoundException
     */
    public function getListOfBatchesByProviderId(int $providerId): Collection
    {
        Log::debug(
            'Start getting list of batches by provider id',
            [
                'providerId' => $providerId
            ]
        );
        // check provider's existence
        $this->providerService->getProviderById($providerId);

        // get list of batches by provider id
        $response = Batch::where('provider_id', $providerId)->get();

        return BatchResource::collection($response);
    }

    /**
     * @param int $providerId
     * @param int $batchId
     * @return BatchResource
     * @throws NotFoundException
     */
    public function getBatchById(int $providerId, int $batchId): BatchResource
    {
        Log::debug(
            'Start getting batch by id',
            [
                'provider_id' => $providerId,
                'batch_id' => $batchId
            ]
        );
        $this->providerService->getProviderById($providerId);

        $batch = Batch::where('id', $batchId)->where('provider_id', $providerId)->first();

        if (empty($batch)) {
            throw NotFoundException::getInstance("Batch with id {$batchId} not found for this provider");
        }

        return new BatchResource($batch);
    }

    /**
     * @param array $data
     * @return void
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function refundProductsToProvider(array $data): void
    {
        Log::debug(
            "Start refunding products to the provider",
            [
                'provider_id' => $data['provider_id'],
                'batch_id' => $data['batch_id'],
            ]
        );

        //check provider, storage and batch existence and get batch
        $this->providerService->getProviderById($data['provider_id']);

        $this->storageService->getStorageById($data['storage_id']);

        $batch = Batch::with('products')
            ->where('provider_id', $data['provider_id'])
            ->find($data['batch_id']);

        if (!$batch) {
            throw NotFoundException::getInstance("Batch with id {$data['batch_id']} not found for this provider");
        }

        //chose method for full or partial refund according to type
        if ($data['type'] === 'full') {
            $this->performFullRefund($batch, $data['storage_id']);
        } else {
            $this->performPartialRefund($batch, $data['products'], $data['storage_id']);
        }
    }

    /**
     * @param Batch $batch
     * @param int $storageId
     * @return void
     */
    private function performFullRefund(Batch $batch, int $storageId): void
    {
        DB::transaction(function () use ($batch, $storageId) {
            // delete products from batch_products
            DB::table('batch_product')
                ->where('batch_id', $batch->id)
                ->delete();

            // delete products from storage
            DB::table('storage_product')->
            where('storage_id', $storageId)->
            whereIn('product_id', $batch->products->pluck('id'))->
            update([
                //because it's full refund, it works
                'quantity' => 0,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * @param Batch $batch
     * @param array $products
     * @param int $storageId
     * @return void
     * @throws BadRequestException
     */
    private function performPartialRefund(Batch $batch, array $products, int $storageId): void
    {
        DB::transaction(function () use ($batch, $products, $storageId) {

            $storageProducts = DB::table('storage_product')->
            where('storage_id', $storageId)->
            whereIn('product_id', collect($products)->pluck('id'))->
            get(['product_id', 'quantity'])->keyBy('product_id');

            $cases = [];
            $ids = [];
            foreach ($products as $product) {
                $currentQuantity = $storageProducts[$product['id']]->quantity;
                if ($product['quantity'] > $currentQuantity) {
                    throw BadRequestException::getInstance(
                        "The quantity of returned product with id {$product['id']} must not exceed the current quantity in storage"
                    );
                }

                $finalQuantity = $currentQuantity - $product['quantity'];

                $cases[] = "WHEN product_id = {$product['id']} AND storage_id = $storageId THEN $finalQuantity";
                $ids[] = $product['id'];
            }

            $cases = implode(' ', $cases);
            $ids = implode(',', $ids);

            DB::statement("
            UPDATE storage_product
            SET
                quantity = CASE
                    $cases
                END,
            updated_at = NOW()
            WHERE storage_id = $storageId AND product_id IN ($ids)
        ");
        });
    }
}
