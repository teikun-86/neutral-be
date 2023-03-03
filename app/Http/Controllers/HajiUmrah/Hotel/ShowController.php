<?php

namespace App\Http\Controllers\HajiUmrah\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\Hotel;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isAdmin = config('app.is_admin');
        
        $hotels = Hotel::query()->with([
            'company',
            'reservations',
            'packages'
        ])
        ->when($isAdmin, function($query) use($request) {
            return $query->orderBy($request->input('order_by', 'created_at'), $request->input('order_direction', 'desc'));
        }, fn($query) => $query->where('company_id', auth()->user()->company_id))
        ->when(!$isAdmin, function ($query) {
            return $query->whereRaw("DATEDIFF(`first_check_in_at`, NOW()) >= 45");
        })
        ->get();

        return response()->json([
            'success' => true,
            'hotels' => $hotels,
        ], 200);
    }
}
