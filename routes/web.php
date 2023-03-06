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
use App\Http\Controllers\User;
use App\Http\Controllers\Airline;
use App\Http\Controllers\Role;
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
    'prefix' => 'airlines',
], function() {
    Route::get('/', Airline\ShowController::class);
    Route::post('/store', Airline\StoreController::class);
    Route::post('/update', Airline\UpdateController::class);
    Route::delete('/delete', Airline\DestroyController::class);
});

Route::group([
    'prefix' => 'hajj-umrah',
    'middleware' => ['auth:sanctum']
], function () {
    Route::group([
        'prefix' => 'flights'
    ], function () {
        Route::get('/', HajiUmrah\Flight\ShowController::class);
        Route::get('/pool', HajiUmrah\Flight\PoolController::class);
        Route::post('/store', HajiUmrah\Flight\StoreController::class)->middleware('permission:haji-umrah.flight-create');
        Route::post('/update', HajiUmrah\Flight\UpdateController::class)->middleware('permission:haji-umrah.flight-update');
        Route::delete('/delete', HajiUmrah\Flight\DestroyController::class)->middleware('permission:haji-umrah.flight-delete');

        Route::group([
            'prefix' => 'reservations'
        ], function () {
            Route::get('/', HajiUmrah\Flight\Reservation\ShowController::class);
            Route::post('/store', HajiUmrah\Flight\Reservation\StoreController::class);
            Route::post('/update', HajiUmrah\Flight\Reservation\UpdateController::class);
            Route::delete('/delete', HajiUmrah\Flight\Reservation\DestroyController::class);
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
        'prefix' => 'hotels'
    ], function() {
        Route::get('/', HajiUmrah\Hotel\ShowController::class);
        Route::post('/store', HajiUmrah\Hotel\StoreController::class);
        Route::post('/update', HajiUmrah\Hotel\UpdateController::class);
        Route::delete('/destroy', HajiUmrah\Hotel\DestroyController::class);

        Route::group([
            'prefix' => 'reservations'
        ], function() {
            Route::get('/', HajiUmrah\Hotel\Reservation\ShowController::class);
            Route::post('/add-payment', HajiUmrah\Hotel\Reservation\AddPaymentController::class);
            Route::post('/store', HajiUmrah\Hotel\Reservation\StoreController::class);
            Route::post('/store-guest-map', HajiUmrah\Hotel\Reservation\StoreGuestMapController::class);
            Route::post('/update', HajiUmrah\Hotel\Reservation\UpdateController::class);
            Route::delete('/destroy', HajiUmrah\Hotel\Reservation\DestroyController::class);
        });
    });


    Route::group([
        'prefix' => 'packages'
    ], function () {
        Route::get('/', HajiUmrah\Package\ShowController::class);
        Route::post('/store', HajiUmrah\Package\StoreController::class);
        Route::post('/update', HajiUmrah\Package\UpdateController::class);
        Route::delete('/destroy', HajiUmrah\Package\DestroyController::class);

        Route::group([
            'prefix' => 'reservations'
        ], function () {
            Route::get('/', HajiUmrah\Package\Reservation\ShowController::class);
            Route::post('/add-payment', HajiUmrah\Package\Reservation\AddPaymentController::class);
            Route::post('/store', HajiUmrah\Package\Reservation\StoreController::class);
            Route::post('/store-manifest', HajiUmrah\Package\Reservation\StoreManifestController::class);
            Route::post('/store-guest-map', HajiUmrah\Package\Reservation\StoreGuestsMapController::class);
            Route::post('/update', HajiUmrah\Package\Reservation\UpdateController::class);
            Route::delete('/destroy', HajiUmrah\Package\Reservation\DestroyController::class);
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
});

Route::group([
    'prefix' => 'payment'
], function() {
    Route::post('/validate', Payment\ValidatePaymentController::class);
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

Route::group([
    'prefix' => 'users'
], function() {
    Route::get('/', User\ShowController::class);
    Route::post('/store', User\StoreController::class);
    Route::post('/update', User\UpdateController::class);
    Route::delete('/destroy', User\DestroyController::class);
});

Route::group([
    'prefix' => 'roles'
], function() {
    Route::get('/', Role\ShowController::class);
    Route::post('/store', Role\StoreController::class);
    Route::post('/update', Role\UpdateController::class);
    Route::delete('/destroy', Role\DestroyController::class);
}); 