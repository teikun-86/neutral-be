<?php

namespace App\Http\Controllers\HajiUmrah\Package\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Package\PackageReservation;
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
            'id.*' => 'required|exists:haji_umrah_package_reservations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $reservations = PackageReservation::whereIn('id', $request->ids)->get();
            foreach ($reservations as $reservation) {
                $reservation->delete();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Reservations deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reservations.',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
