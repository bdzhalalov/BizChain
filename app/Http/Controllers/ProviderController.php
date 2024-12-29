<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderPurchaseRequest;
use App\Http\Services\ProviderService;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    protected ProviderService $providerService;

    public function __construct()
    {
       $this->providerService = new ProviderService();
    }

    public function purchase(ProviderPurchaseRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $this->providerService->purchaseProducts($validatedData);

            return response()->json(['message' => 'Products purchased successfully']);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while purchasing products from provider",
                [
                    'provider_id' => $validatedData['provider_id'],
                    'exception' => $exception->getMessage()
                ]
            );

            return response()->json(['message' => 'Error while purchasing products from provider'], 500);
        }
    }
}
