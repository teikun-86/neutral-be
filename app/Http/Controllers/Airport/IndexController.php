<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        Log::debug("Request to: " . $request->fullUrl());
        
        $airports = Airport::query();

        if ($request->has('iata')) {
            if (is_array($request->iata)) {
                $airports->whereIn('iata', $request->iata);
            } else {
                $airports->where('iata', $request->iata);
            }

            return response()->json([
                'success' => true,
                'airports' => $airports->get()
            ], 200);
        }

        if (!$request->has('intl')) {
            $airports->where('country', "Indonesia");
        }

        return response()->json([
            'success' => true,
            'airports' => $airports->get()
        ], 200);
    }
}
