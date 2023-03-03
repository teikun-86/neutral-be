<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\Hotel;
use App\Models\HajiUmrah\Hotel\HotelReservation;
use Illuminate\Contracts\Cache\LockTimeoutException;
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
            'hotel_id' => 'required|integer|exists:haji_umrah_hotels,id',
            'company_id' => 'required|integer|exists:companies,id',
            'user_id' => [Rule::requiredIf($isAdmin), 'integer', 'exists:users,id'],
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $lock = Cache::lock('hotel-reservation-'.$request->hotel_id, 5);

        try {
            $lock->block(5);
            DB::beginTransaction();
            $hotel = Hotel::whereId($request->hotel_id)->first();
            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => "No hotel found with id {$request->hotel_id}.",
                ], 400);
            }
            
            if ($hotel->packages_left - $request->amount < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package available is not enough.',
                    'errors' => [
                        'amount' => [
                            "The current package available is {$hotel->packages_left}."
                        ]
                    ]
                ], 422);
            }

            $fill = [
                'hotel_id' => $request->hotel_id,
                'amount' => $request->amount,
                'price_per_package' => $hotel->price_per_package,
                'total_price' => $hotel->price_per_package * $request->amount,
                'company_id' => $request->company_id,
                'user_id' => $isAdmin ? $request->user_id : $request->user()->id,
                'expired_at' => now()->addMinutes(15),
            ];
            
            $reservation = new HotelReservation($fill);
            $reservation->save();
            
            DB::commit();
            $lock->release();
            return response()->json([
                'success' => true,
                'message' => 'Hotel reservation created successfully.'
            ], 200);
        } catch(LockTimeoutException $lockException) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hotel reservation.',
                'errors' => $lockException->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hotel reservation.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
