<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_auth')->prefix('v1')->group(function () {
    Route::group(['prefix' => 'providers'], function () {
        Route::get('/', [ProviderController::class, 'list']);

        Route::group(['prefix' => '{providerId}'], function () {
            Route::get('/', [ProviderController::class, 'getById']);
            Route::post('/purchase', [ProviderController::class, 'purchase']);

            Route::group(['prefix' => 'batches'], function () {
                Route::get('/', [BatchController::class, 'listOfBatches']);

                Route::group(['prefix' => '{batchId}'], function () {
                    Route::get('/', [BatchController::class, 'getById']);
                    Route::get('/products', [BatchController::class, 'getBatchProducts']);
                    Route::get('/profit', [BatchController::class, 'profit']);
                    Route::post('/refund', [BatchController::class, 'refund']);
                });
            });
        });
    });

    Route::get('products', [ProductController::class, 'list']);

    Route::group(['prefix' => 'orders'], function () {
        Route::post('/', [OrderController::class, 'create']);
    });

    Route::group(['prefix' => 'storages'], function () {
        Route::get('/', [StorageController::class, 'list']);

        Route::group(['prefix' => '{storageId}'], function () {
            Route::get('/', [StorageController::class, 'getById']);
            Route::get('/remaining-quantity', [StorageController::class, 'getRemainingQuantity']);
        });
    });
});
