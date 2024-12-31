<?php

namespace App\Http\Services;

use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function getListOfAvailableProducts(): Collection
    {
        Log::debug("Start getting list of available products");

        $products = DB::table('product_storage')
            ->join('products', 'product_storage.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id as id',
                'products.name as name',
                'categories.name as category_name',
                'products.price as price',
                DB::raw('SUM(product_storage.quantity) as qty')
            )
            ->groupBy('products.id', 'products.name')
            ->having('qty', '>', 0)
            ->get();

        return ProductResource::collection($products);
    }
}
