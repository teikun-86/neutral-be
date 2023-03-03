<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'seats' => 'required|integer',
            'flight_id' => [Rule::requiredIf(config('app.is_admin'))],
            'user_id' => [Rule::requiredIf(config('app.is_admin'))],
            'company_id' => [Rule::requiredIf(config('app.is_admin'))],
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
                    ->when(!config('app.is_admin'), fn($q) => $q->where('user_id', $request->user()->id))
                    ->first();

                if (!$reservation) {
                    throw new \Exception('reservation.not_found');
                }

                if ($reservation->is_expired) {
                    throw new \Exception('reservation.expired');
                }

                if ($reservation->status == 'paid') {
                    throw new \Exception('reservation.already_paid');
                }

                $toUpdate = [
                    'seats' => $request->seats,
                    'total_price' => $reservation->price_per_seat * $request->seats,
                ];

                if (config('app.is_admin')) {
                    $toUpdate['flight_id'] = $request->flight_id;
                    $toUpdate['user_id'] = $request->user_id;
                    $toUpdate['company_id'] = $request->company_id;
                }

                $reservation->update($toUpdate);
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
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
