<?php

namespace App\Http\Controllers\HajiUmrah\Package\Reservation;

use App\Http\Controllers\Controller;
use App\Imports\HajiUmrah\ManifestImport;
use App\Models\HajiUmrah\Package\PackageReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StoreManifestController extends Controller
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
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'id' => 'required|exists:haji_umrah_flight_reservations,id',
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
            $reservation = PackageReservation::where('id', $request->id)->first();

            if (!$reservation) {
                throw new \Exception('reservation.not_found');
            }

            if ($reservation->hasUnpaidPayment()) {
                throw new \Exception('reservation.has_unpaid');
            }

            if ($reservation->manifest) {
                throw new \Exception('reservation.has_manifest');
            }

            $file = $request->file('file');
            $path = "manifest/pkg{$reservation->id}";
            $ts = now()->timestamp;
            $filename = "MANIFEST-PKG-{$reservation->id}{$ts}.{$file->getClientOriginalExtension()}";
            $file = $file->move(storage_path("app/public/$path"), $filename);

            $manifest = $reservation->manifest()->create([
                'user_id' => $request->user()->id,
                'status' => 'submitted',
                'manifest_file' => asset("storage/$path/{$filename}")
            ]);

            Excel::import(new ManifestImport($manifest), $file);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'manifest.created',
                'data' => $reservation->load('manifest', 'manifest.passengers'),
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'manifest.failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
