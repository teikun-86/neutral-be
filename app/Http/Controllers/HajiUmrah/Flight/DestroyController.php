<?php

namespace App\Http\Controllers\HajiUmrah\Flight;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestroyController extends Controller
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
            $flights = Flight::whereIn('id', $request->ids)->with([
                'reservations',
                'reservations.manifest',
                'reservations.manifest.passenger',
                'reservations.ticket',
            ])->get();
            $flights->map(function($flight) {
                $flight->reservations->map(function($reservation) {
                    $reservation->manifest->map(function($manifest) {
                        $manifest->passenger->delete();
                    });
                    $reservation->manifest->map(function($manifest) {
                        $manifest->delete();
                    });
                    $reservation->ticket->delete();
                    $reservation->delete();
                });
                $flight->delete();
            });
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Flight Deleted'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete flight',
            ], 500);
        }
    }
}
