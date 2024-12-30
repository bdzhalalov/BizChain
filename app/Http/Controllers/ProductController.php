<?php

namespace App\Http\Controllers;

use App\Http\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    protected $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function list(): JsonResponse|Collection
    {
        try {
            return $this->productService->getListOfAvailableProducts();
        } catch (\Throwable $exception) {
            Log::error(
                "Error while getting list of available products",
                [
                    'exception' => $exception->getMessage()
                ]
            );
            return response()->json(['Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
