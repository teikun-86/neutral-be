<?php

namespace App\Http\Controllers\HajiUmrah\Package\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Package\Package;
use App\Models\HajiUmrah\Package\PackageReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isAdmin = config('app.is_admin');
        
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:haji_umrah_packages,id',
            'user_id' => [Rule::requiredIf(config('app.is_admin')), 'exists:users,id'],
            'amount' => 'required|integer|gt:0',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lock = Cache::lock('package-reservation-'.$request->package_id, 10);
        
        try {
            $lock->block(10);
            DB::beginTransaction();
            $package = Package::whereId($request->package_id)->with([
                'hotel',
                'flight',
                'hotel.reservations',
                'flight.reservations',
            ])->first();
            if ($package->packages_left - $request->amount < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'amount' => ['The entered amount is exceeding the packages left.']
                    ]
                ], 422);
            }

            $hotelLeft = $package->hotel->packages_left;
            $flightLeft = $package->flight->available_seats;

            if ($hotelLeft - ($request->amount * $package->hotels_per_package) < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'amount' => ['The entered amount is exceeding the hotel packages left.']
                    ]
                ], 422);
            }

            if ($flightLeft - ($request->amount * $package->seats_per_package) < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'amount' => ['The entered amount is exceeding the flight seats left.']
                    ]
                ], 422);
            }
            
            PackageReservation::create([
                'package_id' => $request->package_id,
                'user_id' => $isAdmin ? $request->user_id : $request->user()->id,
                'amount' => $request->amount,
                'company_id' => $request->company_id,
                'expired_at' => now()->addMinutes(15),
                'price_per_package' => $package->price_per_package,
                'total_price' => $package->price_per_package * $request->amount,
            ]);
            
            DB::commit();
            $lock->release();
            return response()->json([
                'success' => true,
                'message' => 'Package reservation created successfully.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create package reservation.',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
