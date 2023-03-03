<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Exceptions\FlightSeatNotEnoughException;
use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'user_id' => [Rule::requiredIf(config('app.is_admin'))]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validation.failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lock = Cache::lock('flight-reservation-' . $request->flight_id, 5);

        try {
            $lock->block(5);
            
            DB::beginTransaction();
            $flight = Flight::whereId($request->flight_id)->with('reservations')->first();

            if ($flight->depart_at->isPast()) {
                throw new \Exception('flight.invalid');
            }
            
            $check = $flight->available_seats - $request->seats;
            if ($check < 0) {
                throw new FlightSeatNotEnoughException();
            }

            $user_id = config('app.is_admin') ? $request->user_id : $request->user()->id;

            $reservation = $flight->reservations()->create([
                'company_id' => $request->company_id,
                'user_id' => $user_id,
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
            $lock?->release();

            return response()->json([
                'success' => true,
                'message' => 'flight.reservation.stored',
                'data' => $reservation
            ], 200);
        } catch (LockTimeoutException $e) {
            throw new \Exception('flight.reservation.failed');
        } catch (FlightSeatNotEnoughException $fne) {
            return response()->json([
                'success' => false,
                'message' => 'flight.seat_not_enough',
                'errors' => [
                    'seats' => ['flight.seat_not_enough']
                ]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "flight.reservation.failed",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
