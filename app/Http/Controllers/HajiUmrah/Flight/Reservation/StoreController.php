<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Exceptions\FlightSeatNotEnoughException;
use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_id' => 'required|exists:haji_umrah_flights,id',
            'company_id' => 'required|exists:companies,id',
            'seats' => 'required|integer|min:1',
            'pool' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validation.failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $flight = Flight::whereId($request->flight_id)->with('reservations')->first();

            if ($flight->depart_at->isPast()) {
                throw new \Exception('flight.invalid');
            }
            
            $check = $flight->available_seats - $request->seats;
            if ($check < 0) {
                throw new FlightSeatNotEnoughException();
            }

            $reservation = $flight->reservations()->create([
                'company_id' => $request->company_id,
                'user_id' => $request->user()->id,
                'seats' => $request->seats,
                'status' => $request->boolean('pool') ? 'unpaid' : 'active',
                'price_per_seat' => $flight->price,
                'total_price' => $flight->price * $request->seats,
                'expired_at' => now()->addMinutes(15),
                'pool' => $request->boolean('pool'),
            ]);

            $reservation = $reservation->load([
                'flight',
                'company',
                'user'
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'flight.reservation.stored',
                'data' => $reservation
            ], 200);
        } catch (\Throwable $th) {
            $message = $th instanceof FlightSeatNotEnoughException
                ? 'flight.seat_not_enough'
                : 'flight.reservation.failed';

            $code = $th instanceof FlightSeatNotEnoughException
                ? 400
                : 500;
                
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $th->getMessage(),
            ], $code);
        }
    }
}
