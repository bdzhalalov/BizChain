<?php

namespace App\Http\Services;

use App\Exceptions\NotFoundException;
use App\Http\Resources\StorageProductQuantityResource;
use App\Http\Resources\StorageResource;
use App\Models\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StorageService
{
    /**
     * @return Collection
     */
    public function getListOfStorages(): Collection
    {
        Log::debug('Start getting list of storages');

        $storages = Storage::all();

        return StorageResource::collection($storages);
    }

    /**
     * @param int $storageId
     * @return StorageResource
     * @throws NotFoundException
     */
    public function getStorageById(int $storageId): StorageResource
    {
        Log::debug(
            'Start getting storage by id',
            [
                'storage_id' => $storageId
            ]
        );

        $storage = Storage::where('id', $storageId)->first();

        if (empty($storage)) {
            throw NotFoundException::getInstance("Storage with id {$storageId} not found");
        }

        return new StorageResource($storage);
    }

    /**
     * @param array $data
     * @return Collection
     * @throws NotFoundException
     */
    public function getRemainingProductQuantityByStorageId(array $data): Collection
    {
        // check storage existence
        $this->getStorageById($data['storage_id']);

        //get remaining product quantity by storage
        $remainingQuantities = DB::table('product_storage')
            ->join('products', 'product_storage.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(product_storage.quantity) as total_quantity')
            )
            ->whereBetween('product_storage.updated_at', [$data['start_date'], $data['end_date']])
            ->where('product_storage.storage_id', $data['storage_id'])
            ->groupBy('products.id', 'products.name', 'product_storage.storage_id')
            ->get();

        return StorageProductQuantityResource::collection($remainingQuantities);
    }
}
