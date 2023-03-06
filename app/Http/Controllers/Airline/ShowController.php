<?php

namespace App\Http\Controllers\Airline;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Airline::get()
        ], 200);
    }
}
