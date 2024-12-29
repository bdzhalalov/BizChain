<?php

namespace App\Http\Services;

use App\Exceptions\NotFoundException;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\Log;

class BatchService
{
    protected ProviderService $providerService;

    public function __construct()
    {
        $this->providerService = new ProviderService();
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
}
