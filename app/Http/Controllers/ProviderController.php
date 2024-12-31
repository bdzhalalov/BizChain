<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\ProviderPurchaseRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Services\ProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProviderController extends Controller
{
    protected ProviderService $providerService;

    public function __construct()
    {
       $this->providerService = new ProviderService();
    }

    /**
     * @return JsonResponse|Collection
     */
    public function list(): JsonResponse|Collection
    {
        try {
            return $this->providerService->getListOfProviders();
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting list of providers",
                [
                    'exception' => $exception->getMessage(),
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $providerId
     * @return JsonResponse|ProviderResource
     */
    public function getById(int $providerId): JsonResponse|ProviderResource
    {
        try {
          return $this->providerService->getProviderById($providerId);
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
     * @param ProviderPurchaseRequest $request
     * @param int $providerId
     * @return JsonResponse
     */
    public function purchase(ProviderPurchaseRequest $request, int $providerId): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $validatedData['provider_id'] = $providerId;

            $this->providerService->purchaseProducts($validatedData);

            return response()->json(['message' => 'Products purchased successfully']);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while purchasing products from provider",
                [
                    'provider_id' => $providerId,
                    'exception' => $exception->getMessage()
                ]
            );

            return response()->json(
                ['message' => 'Error while purchasing products from provider'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
