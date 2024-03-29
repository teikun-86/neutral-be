<?php

namespace App\Http\Controllers\Destination\Tour;

use App\Http\Controllers\Controller;
use App\Models\Destination;
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
        $domestic = Destination::with('country', 'city', 'province')
                ->whereRelation('country', 'name', 'Indonesia')
                ->inRandomOrder()->limit(10)->get();
        $international = Destination::with('country', 'city', 'province')
                ->whereRelation('country', 'name', '!=', 'Indonesia')
                ->inRandomOrder()->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'domestic' => $domestic,
                'international' => $international
            ]
        ], 200);
    }
}
