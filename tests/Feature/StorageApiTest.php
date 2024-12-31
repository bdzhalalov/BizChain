<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestCase;

class StorageApiTest extends ApiTestCase
{
    /** @test */
    public function test_can_get_list_of_storages(): void
    {
        $response = $this->getJson(
            '/api/v1/storages',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([[
            'id',
            'name'
        ]]);
    }

    /** @test */
    public function test_return_forbidden_when_getting_list_of_storages_without_key(): void
    {
        $response = $this->getJson(
            '/api/v1/storages',
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_get_single_storage(): void
    {
        $response = $this->getJson(
            '/api/v1/storages/1',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name'
        ]);
    }

    /** @test */
    public function test_return_forbidden_when_getting_single_storage_without_key(): void
    {
        $response = $this->getJson(
            '/api/v1/storages/1',
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_return_not_found_if_get_unexisting_storage(): void
    {
        $response = $this->getJson(
            '/api/v1/storages/123',
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function test_can_get_remaining_product_quantity_from_storage(): void
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $response = $this->getJson(
            "/api/v1/storages/1/remaining-quantity?start_date=$startDate&end_date=$endDate",
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function test_return_forbidden_when_getting_remaining_quantity_without_key(): void
    {
        $response = $this->getJson(
            "/api/v1/storages/1/remaining-quantity",
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function test_return_validation_error_when_dates_are_not_provided(): void
    {
        $response = $this->getJson(
            "/api/v1/storages/1/remaining-quantity",
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    public function test_return_validation_error_when_end_date_earler_than_start_date(): void
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate. " - 2 day"));
        $response = $this->getJson(
            "/api/v1/storages/1/remaining-quantity?start_date=$startDate&end_date=$endDate",
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(422);
    }

    public function test_return_not_found_when_get_unexisting_storage(): void
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $response = $this->getJson(
            "/api/v1/storages/123/remaining-quantity?start_date=$startDate&end_date=$endDate",
            [
                'x-api-key' => env('API_KEY')
            ]
        );

        $response->assertStatus(404);
    }
}
