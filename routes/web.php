<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Airport;
use App\Http\Controllers\Flight;
use App\Http\Controllers\Profile;
use App\Http\Controllers\Destination;
use App\Models\Country;
use App\Http\Controllers\HajiUmrah;
use App\Http\Controllers\Payment;
use App\Models\Company;

Route::get('/', function () {
    return [
        'success' => true,
        'app_name' => config('app.name'),
        'app_version' => config('app.version'),
    ];
});

Route::get('/home', function() {
    return redirect(config('app.frontend_url'));
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $user->load([
        'roles' => function($q) {
            return $q->with(['permissions']);
        },
        'company',
        'country'
    ]);

    $user->roles = $user->roles->map(function($role) {
        $role->setHidden([
            'pivot',
            'id',
            'created_at',
            'updated_at'
        ]);
        $role->permissions = $role->permissions->map(function($permission) {
            $permission->setHidden([
                'pivot',
                'id',
                'created_at',
                'updated_at'
            ]);
        });
        return $role;
    });
    
    return $user;
});

Route::get('/airports', Airport\IndexController::class);

Route::group([
    'prefix' => 'destinations',
], function() {
    Route::get('/', Destination\ShowController::class);
    Route::get('/tour', Destination\Tour\ShowController::class);
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

Route::get('/countries', function() {
    return response()->json([
        'success' => true,
        'data' => Country::get()
    ], 200);
});

Route::get('/companies', function() {
    return response()->json([
        'success' => true,
        'data' => Company::get()
    ], 200);
});

Route::group([
    'prefix' => 'payment'
], function() {
    Route::group([
        'prefix' => 'payment-methods'
    ], function() {
        Route::get('/', Payment\PaymentMethod\ShowController::class);
        Route::post('/store', Payment\PaymentMethod\StoreController::class)
            ->middleware(['auth:sanctum', 'permission:payment-method-create']);
        Route::post('/update', Payment\PaymentMethod\UpdateController::class)
            ->middleware(['auth:sanctum', 'permission:payment-method-update']);
        Route::delete('/destroy', Payment\PaymentMethod\DestroyController::class)
            ->middleware(['auth:sanctum', 'permission:payment-method-delete']);
    });
});