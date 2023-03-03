<?php

namespace App\Http\Controllers\HajiUmrah\Package;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use App\Models\HajiUmrah\Hotel\Hotel;
use App\Models\HajiUmrah\Package\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_id' => 'required|exists:haji_umrah_flights,id',
            'hotel_id' => 'required|exists:haji_umrah_hotels,id',
            'packages_available' => 'required|integer|gt:0',
            'seats_per_package' => 'required|integer|gt:0',
            'hotels_per_package' => 'required|integer|gt:0',
            'price_per_package' => 'required|integer|gt:0',
            'program_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $flight = Flight::whereId($request->flight_id)->first();
            $hotel = Hotel::whereId($request->hotel_id)->first();

            if ($flight->depart_at->isPast()) throw new \Exception("The selected flight is in the past.");

            if ($hotel->first_check_in_at->isPast()) throw new \Exception("The selected hotel is in the past.");

            $seats = $request->packages_available * $request->seats_per_package;
            $hotels = $request->packages_available * $request->hotels_per_package;

            $flightNotEnough = $flight->available_seats < $seats;
            $hotelNotEnough = $hotel->packages_left < $hotels;

            if ($flightNotEnough || $hotelNotEnough) {
                $message = [];
                if ($flightNotEnough) $message['flight_id'][] = "The selected flight doesn't have enough seats.";
                if ($hotelNotEnough) $message['hotel_id'][] = "The selected hotel doesn't have enough packages.";
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $message,
                ], 422);
            }

            Package::create([
                'flight_id' => $request->flight_id,
                'hotel_id' => $request->hotel_id,
                'packages_available' => $request->packages_available,
                'seats_per_package' => $request->seats_per_package,
                'hotels_per_package' => $request->hotels_per_package,
                'price_per_package' => $request->price_per_package,
                'program_type' => $request->program_type,
            ]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Package created successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Failed to create package.",
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
