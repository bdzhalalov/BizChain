<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\ApiTestCase;

class OrderApiTest extends ApiTestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->payload = [
            'client_id' => 1,
            'products' => [
                [
                    'id' => 1,
                    'qty' => 0
                ]
            ]
        ];
    }

    /** @test */
    public function test_can_create_order(): void
    {
        $quantity = mt_rand(1, $this->productQuantity);

        $this->payload['products'][0]['qty'] = $quantity;

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(201);

        $expectedQuantity = $this->productQuantity - $quantity;

        $actualQuantity = DB::table('product_storage')
            ->where('product_id', 1)
            ->first('quantity');

        $this->assertEquals($expectedQuantity, $actualQuantity->quantity);
    }

    /** @test */
    public function test_return_forbidden_when_creating_order_without_key(): void
    {
        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_client_id(): void
    {
        $this->payload['client_id'] = 123;
        $this->payload['products'][0]['qty'] = 1;

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_validation_error_when_client_id_is_undefined(): void
    {
        unset($this->payload['client_id']);
        $this->payload['products'][0]['qty'] = 1;

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_products_are_undefined(): void
    {
        unset($this->payload['products']);

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_quantity_too_small(): void
    {
        $this->payload['products'][0]['qty'] = 0;

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(422);
    }

    public function test_return_bad_request_when_get_overstated_quantity(): void
    {
        $quantity = $this->productQuantity + mt_rand(1, 10);
        $this->payload['products'][0]['qty'] = $quantity;

        $response = $this->postJson(
            '/api/v1/orders',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(400);
    }
}
