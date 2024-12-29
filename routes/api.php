<?php

use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_auth')->prefix('v1')->group(function () {
    Route::group(['prefix' => 'providers'], function () {
        Route::post('/purchase', [ProviderController::class, 'purchase']);
    });
});
