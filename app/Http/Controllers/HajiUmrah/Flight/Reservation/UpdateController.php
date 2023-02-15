<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
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
            'reservation_id' => 'required|exists:haji_umrah_flight_reservations,id',
            'seats' => 'required|integer'
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
                $reservation = FlightReservation::where('id', $request->reservation_id)
                    ->where('user_id', $request->user()->id)
                    ->first();

                if (!$reservation) {
                    throw new \Exception('reservation.not_found');
                }

                if ($reservation->expired_at->isPast()) {
                    throw new \Exception('reservation.expired');
                }

                if ($reservation->status == 'paid') {
                    throw new \Exception('reservation.already_paid');
                }

                $reservation->update([
                    'seats' => $request->seats,
                    'total_price' => $reservation->price_per_seat * $request->seats,
                ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'reservation.updated',
                'reservation' => $reservation,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'reservation.update_failed',
            ], 500);
        }
    }
}
