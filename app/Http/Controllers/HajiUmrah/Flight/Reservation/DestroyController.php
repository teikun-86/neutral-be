<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $reservations = FlightReservation::whereIn('id', $request->ids)->get();
            $reservations->map(function($reservation) {
                if ($reservation->manifest) {
                    $reservation->manifest->passengers()->delete();
                    $reservation->manifest->delete();
                }
                if ($reservation->ticket) {
                    $reservation->ticket->delete();
                }
                $reservation->delete();
            });
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Flight Reservation Deleted'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete flight reservation',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}