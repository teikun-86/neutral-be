<?php

namespace App\Http\Controllers\HajiUmrah\Visa;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Visa\VisaApplication;
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
        $user = $request->user();

        $visas = VisaApplication::query();
        
        if (!$request->boolean('all')) {
            $visas = $visas->where('user_id', $user->id);
        }

        $visas = $visas->with(['user', 'company', 'applicants'])->get();

        return response()->json([
            'success' => true,
            'data' => $visas,
        ], 200);
    }
}
