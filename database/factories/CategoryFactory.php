<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

        ];
    }

    public function withProvider(Provider $provider)
    {
        return $this->state(function (array $attributes) use ($provider) {
            return [
                'name' => $provider->name,
                'provider_id' => $provider->id,
                'parent_id' => null,
            ];
        });
    }

    public function withParent(Category $category)
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'name' => $category->name . '_child',
                'parent_id' => $category->id,
                'provider_id' => $category->provider_id,
            ];
        });
    }
}
