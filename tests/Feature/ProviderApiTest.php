<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestCase;

class ProviderApiTest extends ApiTestCase
{
    private $payload;
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->payload = [
            'storage_id' => 1,
            'products' => [
                [
                    'name' => 'Test product',
                    'category' => 'Milk',
                    'quantity' => 1,
                    'purchase_price' => 100,
                ]
            ]
        ];
    }

    /** @test */
    public function test_can_get_list_of_providers(): void
    {
        $response = $this->getJson('/api/v1/providers', [
            'x-api-key' => env('API_KEY')
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_return_forbidden_when_send_request_without_key(): void
    {
        $response = $this->getJson('/api/v1/providers');

        $response->assertStatus(403);
    }

    /** @tesst */
    public function test_can_get_provider_by_id(): void
    {
        $response = $this->getJson('/api/v1/providers/1', [
            'x-api-key' => env('API_KEY')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
        ]);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_provider(): void
    {
        $response = $this->getJson('/api/v1/providers/345', [
            'x-api-key' => env('API_KEY')
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_forbidden_when_get_provider_without_key(): void
    {
        $response = $this->getJson('/api/v1/providers/1');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_purchase_products_from_provider(): void
    {
        $response = $this->postJson(
            '/api/v1/providers/1/purchase',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function test_return_forbidden_when_purchase_products_from_provider_without_key(): void
    {
        $response = $this->postJson(
            '/api/v1/providers/1/purchase',
            $this->payload,
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_return_validation_error_when_purchase_products_without_storage_id(): void
    {
        unset($this->payload['storage_id']);

        $response = $this->postJson(
            '/api/v1/providers/1/purchase',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_purchase_products_without_products(): void
    {
        unset($this->payload['products']);

        $response = $this->postJson(
            '/api/v1/providers/1/purchase',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_not_found_when_purchase_products_with_unexisting_storage_id(): void
    {
        $this->payload['storage_id'] = 400;

        $response = $this->postJson(
            '/api/v1/providers/1/purchase',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_not_found_when_purchase_products_from_unexisting_provider(): void
    {
        $response = $this->postJson(
            '/api/v1/providers/123/purchase',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(404);
    }
}
