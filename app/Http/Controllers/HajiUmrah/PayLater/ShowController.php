<?php

namespace App\Http\Controllers\HajiUmrah\PayLater;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PayLater;
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
        
        $payLater = PayLater::query()->with(['user', 'provider']);

        if ($request->has('id')) {
            $payLater = $payLater->where('id', $request->id);
        }

        if (!$request->boolean('all')) {
            $payLater = $payLater->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'data' => $payLater->get(),
        ], 200);
    }
}
