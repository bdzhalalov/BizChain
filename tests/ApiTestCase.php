<?php

namespace Tests;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Storage;
use Database\Seeders\ProviderSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class ApiTestCase extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $provider = Provider::factory()->create();
        Storage::factory(5)->create();
        Category::factory()->withProvider($provider)->create();
        Batch::factory()->withProvider($provider)->create();
    }

    public function tearDown(): void
    {
        $this->artisan('db:wipe');
    }
}
