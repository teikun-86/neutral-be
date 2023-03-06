<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\HotelReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreGuestMapController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:haji_umrah_hotel_reservations,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx',
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
            $reservation = HotelReservation::where('id', $request->id)->first();
            if ($reservation->amount_paid === 0) {
                throw new \Exception('reservation.unpaid');
            }
            $file = $request->file('file');
            $name = "GMAP-{$reservation->id}-" . time() . '.' . $file->getClientOriginalExtension();
            $path = "gmap/{$reservation->id}";
            $file->move(storage_path("app/public/{$path}"), $name);
            $reservation->update([
                'guests_map' => asset("storage/{$path}/{$name}")
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Guest Map Uploaded'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload guest map',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
