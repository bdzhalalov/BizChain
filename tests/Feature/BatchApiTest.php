<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\ApiTestCase;

class BatchApiTest extends ApiTestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->payload = [
            'type' => 'partial',
            'storage_id' => 1,
            'products' => [
                [
                    'id' => 1,
                    'quantity' => 0
                ]
            ]
        ];
    }

    /** @test */
    public function test_can_get_batch_list(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/1/batches',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            ['id']
        ]);
    }

    /** @test */
    public function test_return_forbidden_when_get_batch_list_without_key(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/1/batches',
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_return_not_found_when_get_batch_list_for_unexisting_provider(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/123/batches',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_can_get_single_batch(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/1/batches/1',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id'
        ]);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_batch(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/1/batches/130',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_not_found_when_get_single_batch_for_unexisting_provider(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/102/batches/1',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_forbidden_when_get_single_batch_without_key(): void
    {
        $response = $this->getJson(
            '/api/v1/providers/1/batches/1',
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_perform_partial_refund_to_provider(): void
    {
        $quantity = mt_rand(1, $this->productQuantity);

        $this->payload['products'][0]['quantity'] = $quantity;

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(200);

        $expectedQuantity = $this->productQuantity - $quantity;

        $actualQuantity = DB::table('product_storage')
            ->where('product_id', 1)
            ->first('quantity');

        $this->assertEquals($expectedQuantity, $actualQuantity->quantity);
    }

    /** @test */
    public function test_can_perform_full_refund_to_provider(): void
    {
        $this->payload['type'] = 'full';
        unset($this->payload['products']);

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(200);

        $actualQuantity = DB::table('product_storage')
            ->where('product_id', 1)
            ->first('quantity');

        $this->assertEquals(0, $actualQuantity->quantity);
    }

    /** @test */
    public function test_return_forbidden_when_performing_refund_without_key(): void
    {
       $response = $this->postJson(
           '/api/v1/providers/1/batches/1/refund',
           $this->payload
       );

       $response->assertStatus(403);
    }

    /** @test */
    public function test_return_validation_error_when_field_type_is_undefined(): void
    {
        unset($this->payload['type']);

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_storage_id_is_undefined(): void
    {
        unset($this->payload['storage_id']);

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_products_are_undefined_for_partial_refund(): void
    {
        unset($this->payload['products']);

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_validation_error_when_product_not_found(): void
    {
        $this->payload['products'][0]['id'] = 123;

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_provider_for_refund(): void
    {
        $quantity = mt_rand(1, $this->productQuantity);

        $this->payload['products'][0]['quantity'] = $quantity;

        $response = $this->postJson(
            '/api/v1/providers/123/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_batch_for_refund(): void
    {
        $quantity = mt_rand(1, $this->productQuantity);

        $this->payload['products'][0]['quantity'] = $quantity;

        $response = $this->postJson(
            '/api/v1/providers/1/batches/123/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_not_found_when_get_unexisting_storage_for_refund(): void
    {
        $quantity = mt_rand(1, $this->productQuantity);

        $this->payload['products'][0]['quantity'] = $quantity;
        $this->payload['storage_id'] = 123;

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_return_bad_request_when_get_overstated_quantity_for_partial_refund(): void
    {
        $quantity = $this->productQuantity + mt_rand(1, 10);

        $this->payload['products'][0]['quantity'] = $quantity;

        $response = $this->postJson(
            '/api/v1/providers/1/batches/1/refund',
            $this->payload,
            [
                'x-api-key' => env('API_KEY')
            ],
        );

        $response->assertStatus(400);
    }
}
