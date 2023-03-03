<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\Hotel;
use App\Models\HajiUmrah\Hotel\HotelReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isAdmin = config('app.is_admin');

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:haji_umrah_hotel_reservations,id',
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

        try {
            DB::beginTransaction();
            $hotel = Hotel::whereId($request->hotel_id)->first();

            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => "No hotel found with id {$request->hotel_id}.",
                ], 400);
            }

            $reservation = HotelReservation::whereId($request->id)->first();

            $available = $hotel->packages_left + $reservation->amount;

            if ($available - $request->amount < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package available is not enough.',
                    'errors' => [
                        'amount' => [
                            "The current package available is {$available}."
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
            ];
            $reservation->fill($fill);
            $reservation->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Hotel reservation updated successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hotel reservation.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
