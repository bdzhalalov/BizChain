<?php

namespace Tests\Feature;

use Tests\ApiTestCase;

class ProductApiTest extends ApiTestCase
{
    /** @test */
    public function test_can_get_list_of_products(): void
    {
        $response = $this->getJson(
            '/api/v1/products',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);
    }

    public function test_return_forbidden_when_getting_list_of_products_without_key(): void
    {
        $response = $this->getJson(
            '/api/v1/products',
        );

        $response->assertStatus(403);
    }
}
