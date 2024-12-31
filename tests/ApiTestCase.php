<?php

namespace Tests;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class ApiTestCase extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public $productQuantity;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $provider = Provider::factory()->create();
        $storage = Storage::factory()->create();
        $category = Category::factory()->withProvider($provider)->create();
        $batch = Batch::factory()->withProvider($provider)->create();
        $product = Product::factory()->withCategory($category)->create();
        Client::factory()->create();

        $this->productQuantity = mt_rand(2, 50);

        DB::table('batch_product')->insert([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => $this->productQuantity,
            'purchase_price' => 30,
        ]);

        DB::table('product_storage')->insert([
            'product_id' => $product->id,
            'storage_id' => $storage->id,
            'quantity' => $this->productQuantity,
        ]);
    }

    public function tearDown(): void
    {
        $this->artisan('db:wipe');
    }
}
