<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Airport;
use App\Http\Controllers\Flight;
use App\Http\Controllers\Profile;
use App\Http\Controllers\Destination;

Route::get('/', function () {
    return [
        'success' => true,
        'app_name' => config('app.name'),
        'app_version' => config('app.version'),
        'production' => app()->isProduction(),
    ];
});

Route::get('/home', function() {
    return redirect(config('app.frontend_url'));
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/airports', Airport\IndexController::class);

Route::group([
    'prefix' => 'destinations',
], function() {
    Route::get('/', Destination\ShowController::class);
    Route::get('/tour', Destination\TourController::class);
});

Route::group([
    'prefix' => 'flight',
], function() {
    Route::get('/search', Flight\SearchController::class);
    Route::post('/prebook', Flight\PrebookController::class);
    Route::post('/book', Flight\BookController::class);
});

Route::group([
    'prefix' => 'cart',
], function() {
    Route::post('/store', Flight\Cart\StoreController::class);
    Route::get('/{id}', Flight\Cart\ShowController::class);
    Route::post('/{id}/prebook', Flight\Cart\PrebookController::class);
});

Route::group([
    'prefix' => 'profile',
    'middleware' => 'auth:sanctum',
], function() {
    Route::post('/update', Profile\UpdateController::class);
    Route::post('/update-credentials', Profile\CredentialsController::class);
});