<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    public function withCategory(Category $category): self
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'name' => 'test_product',
                'category_id' => $category->id,
                'price' => $this->faker->randomFloat(2, 10, 100),
            ];
        });
    }
}
