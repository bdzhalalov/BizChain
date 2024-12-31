<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\StorageRequest;
use App\Http\Resources\StorageResource;
use App\Http\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StorageController extends Controller
{
    protected StorageService $storageService;

    public function __construct()
    {
        $this->storageService = new StorageService();
    }

    /**
     * @return JsonResponse|Collection
     */
    public function list(): JsonResponse|Collection
    {
        try {
           return $this->storageService->getListOfStorages();
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting list of storages",
                [
                    'exception' => $exception->getMessage(),
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $storageId
     * @return StorageResource|JsonResponse
     */
    public function getById(int $storageId): StorageResource|JsonResponse
    {
        try {
            return $this->storageService->getStorageById($storageId);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting provider by id",
                [
                    'exception' => $exception->getMessage(),
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StorageRequest $request
     * @param int $storageId
     * @return JsonResponse|Collection
     */
    public function getRemainingQuantity(StorageRequest $request, int $storageId): JsonResponse|Collection
    {
        try {
            $validatedData = $request->validated();
            $validatedData['storage_id'] = $storageId;

            return $this->storageService->getRemainingProductQuantityByStorageId($validatedData);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting provider by id",
                [
                    'exception' => $exception->getMessage(),
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
