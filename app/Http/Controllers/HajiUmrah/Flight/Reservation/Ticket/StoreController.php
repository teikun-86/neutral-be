<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation\Ticket;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Http\Request;
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
            'flight_reservation_id' => 'required|exists:haji_umrah_flight_reservations,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png'
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
            $reservation = FlightReservation::where('id', $request->flight_reservation_id)->first();
            if (!$reservation) {
                throw new \Exception('reservation.not_found');
            }

            $file = $request->file('file');
            $path = "tickets/{$reservation->id}";
            $ts = now()->timestamp;
            $filename = "TICKET-{$reservation->id}{$ts}.{$file->getClientOriginalExtension()}";
            $file = $file->move(storage_path("app/public/$path"), $filename);

            $reservation->ticket()->create([
                'ticket_path' => asset("storage/$path/$filename")
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'ticket.created',
                'data' => $reservation->ticket
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ticket.failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
