<?php

namespace App\Http\Services;

use App\Models\Category;

class CategoryService
{
    public function getProductsCategories(array $products): array
    {
        // get unique categories from products
        $categories = collect($products)->pluck('category')->unique();

        // get existing categories
        $existingCategories = Category::whereIn('name', $categories)->pluck('id', 'name');

        // get new categories
        $newCategories = $categories->diff($existingCategories->keys())->map(fn($name) => ['name' => $name]);

        if ($newCategories->isNotEmpty()) {
            Category::insert($newCategories->toArray());
        }

        return Category::whereIn('name', $categories)->pluck('id', 'name')->toArray();
    }
}
