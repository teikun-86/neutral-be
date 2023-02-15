<?php

namespace App\Http\Controllers\HajiUmrah\PayLater\Provider;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PaylaterProvider;
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
        $providers = PaylaterProvider::get();

        return response()->json([
            'success' => true,
            'data' => $providers,
        ], 200);
    }
}
