<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Destination;
use App\Http\Controllers\HajiUmrah;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'destinations',
], function () {
    Route::group([
        'prefix' => 'tour',
    ], function() {
        Route::post('/store', Destination\Tour\StoreController::class)->middleware('auth:sanctum');
    });

    Route::post('/store', Destination\StoreController::class)->middleware('auth:sanctum');
});


