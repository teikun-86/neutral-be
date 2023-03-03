<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\HotelReservation;
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
            'ids' => 'required|array',
            'id.*' => 'required|exists:haji_umrah_hotel_reservations,id',
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
            $reservations = HotelReservation::whereIn('id', $request->ids)->get();
            $reservations->map(fn($res) => $res->delete());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reservation.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
