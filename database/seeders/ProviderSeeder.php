<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Provider;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provider = Provider::factory()->count(5)->create();
        foreach ($provider as $item) {
            Category::factory()->withProvider($item)->create();
        }
    }
}
