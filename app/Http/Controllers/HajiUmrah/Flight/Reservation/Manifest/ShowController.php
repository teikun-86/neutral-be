<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation\Manifest;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightManifest;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $manifest = FlightManifest::query();

        if ($request->has('withPassengers')) {
            $manifest = $manifest->with('passengers');
        }

        if ($request->has('id')) {
            $manifest = $manifest->where('id', $request->id);

            return response()->json([
                'success' => true,
                'manifest' => $manifest->first()
            ], 200);
        }

        if ($request->has('reservation_id')) {
            $manifest = $manifest->where('reservation_id', $request->reservation_id);
        }

        return response()->json([
            'success' => true,
            'manifests' => $manifest->get()
        ], 200);
    }
}
