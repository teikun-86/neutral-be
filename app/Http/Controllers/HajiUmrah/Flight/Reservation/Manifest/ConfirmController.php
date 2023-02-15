<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation\Manifest;

use App\Http\Controllers\Controller;
use App\Imports\HajiUmrah\ManifestImport;
use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ConfirmController extends Controller
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
            'file' => 'nullable|file|mimes:xlsx,xls,csv',
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
            $reservation = FlightReservation::where('id', $request->flight_reservation_id)->with('manifest', 'manifest.passengers')->first();
            $manifest = $reservation->manifest;

            if ($manifest->status == "confirmed") {
                throw new \Exception('manifest.already_confirmed');
            }
            
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = "manifest/{$request->flight_reservation_id}";
                $ts = now()->timestamp;
                $filename = "MANIFEST-{$request->flight_reservation_id}{$ts}.{$file->getClientOriginalExtension()}";
                $file = $file->move(storage_path("app/public/$path"), $filename);

                $manifest->passengers()->delete();

                Excel::import(new ManifestImport($manifest), $file);

                $manifest->manifest_file = asset("storage/$path/$filename");
            }
            $manifest->status = "confirmed";
            $manifest->save();
            $result = $reservation->load('manifest', 'manifest.passengers');
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'manifest.confirmed',
                'data' => $result
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'manifest.confirm_failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
