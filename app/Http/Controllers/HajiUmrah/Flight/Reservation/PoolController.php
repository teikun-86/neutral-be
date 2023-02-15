<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Exceptions\FlightSeatNotEnoughException;
use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PoolController extends Controller
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validation.failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = null;
            Cache::lock('flight-reservation-' . $request->flight_id)
                ->block(5, function () use ($request, &$result) {
                    DB::transaction(function () use ($request, &$result) {
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
                            'status' => 'active',
                            'price_per_seat' => $flight->price,
                            'total_price' => $flight->price * $request->seats,
                            'expired_at' => now()->addMinutes(15),
                        ]);

                        $result = $reservation->load([
                            'flight',
                            'company',
                            'user'
                        ]);
                    });
                });

            return response()->json([
                'success' => true,
                'message' => 'flight.reservation.stored',
                'data' => $result
            ], 200);
        } catch (\Throwable $th) {
            $message = $th instanceof FlightSeatNotEnoughException
                ? 'flight.seat.not_enough'
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
