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


Route::group([
    'prefix' => 'hajj-umrah',
    'middleware' => ['auth:sanctum', 'user-type:agent,company']
],
    function () {
        Route::group([
            'prefix' => 'flights'
        ], function () {
            Route::get('/', HajiUmrah\Flight\ShowController::class)->middleware('has-company');
            Route::get('/pool', HajiUmrah\Flight\PoolController::class);
            Route::post('/store', HajiUmrah\Flight\StoreController::class);
            Route::post('/update', HajiUmrah\Flight\UpdateController::class);

            Route::group([
                'prefix' => 'reservations'
            ], function () {
                Route::get('/', HajiUmrah\Flight\Reservation\ShowController::class);
                Route::post('/store', HajiUmrah\Flight\Reservation\StoreController::class);
                Route::post('/add-payment', HajiUmrah\Flight\Reservation\AddPaymentController::class);

                Route::group([
                    'prefix' => 'manifest'
                ], function () {
                    Route::get('/', HajiUmrah\Flight\Reservation\Manifest\ShowController::class);
                    Route::post('/store', HajiUmrah\Flight\Reservation\Manifest\StoreController::class);
                    Route::post('/confirm', HajiUmrah\Flight\Reservation\Manifest\ConfirmController::class);
                });
                Route::group([
                    'prefix' => 'ticket'
                ], function () {
                    Route::post('/store', HajiUmrah\Flight\Reservation\Ticket\StoreController::class);
                });
            });
        });

        Route::group([
            'prefix' => 'visa',
        ], function () {
            Route::get('/', HajiUmrah\Visa\ShowController::class);
            Route::post('/store', HajiUmrah\Visa\StoreController::class);
            Route::post('/update', HajiUmrah\Visa\UpdateController::class);
            Route::delete('/destroy', HajiUmrah\Visa\DestroyController::class);
        });

        Route::group([
            'prefix' => 'pay-later',
        ], function () {
            Route::get('/', HajiUmrah\PayLater\ShowController::class);
            Route::post('/store', HajiUmrah\PayLater\StoreController::class);
            Route::post('/update', HajiUmrah\PayLater\UpdateController::class);
            Route::delete('/destroy', HajiUmrah\PayLater\DestroyController::class);

            Route::group([
                'prefix' => 'provider'
            ], function () {
                Route::get('/', HajiUmrah\PayLater\Provider\ShowController::class);
                Route::post('/store', HajiUmrah\PayLater\Provider\StoreController::class);
                Route::post('/update', HajiUmrah\PayLater\Provider\UpdateController::class);
                Route::delete('/destroy', HajiUmrah\PayLater\Provider\DestroyController::class);
            });
        });
    }
);