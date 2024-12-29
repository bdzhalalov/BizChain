<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Resources\BatchResource;
use App\Http\Services\BatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BatchController extends Controller
{
    protected BatchService $batchService;

    public function __construct()
    {
        $this->batchService = new BatchService();
    }

    /**
     * @param int $providerId
     * @return JsonResponse|Collection
     */
    public function listOfBatches(int $providerId): JsonResponse|Collection
    {
        try {
           return $this->batchService->getListOfBatchesByProviderId($providerId);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting list of batches",
                [
                    'provider_id' => $providerId,
                    'exception' => $exception->getMessage()
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $providerId
     * @param int $batchId
     * @return BatchResource|JsonResponse
     */
    public function getById(int $providerId, int $batchId): BatchResource|JsonResponse
    {
        try {
            return $this->batchService->getBatchById($providerId, $batchId);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting batch by id",
                [
                    'provider_id' => $providerId,
                    'exception' => $exception->getMessage()
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getBatchProducts(int $providerId, int $batchId): void
    {
        // The method is needed to receive the batch products,
        // so that later we can know the product IDs for a partial refund
        // Now this method is created to demonstrate where the product IDs were obtained from when performing a refund
        //TODO: add method realization
    }
}