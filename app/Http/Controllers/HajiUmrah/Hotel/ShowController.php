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
        }, function($query) use($request) {
            $q = $query;
            if (!$request->boolean('pool')) {
                $q = $q->where('company_id', auth()->user()->company_id);
            } else {
                $q = $q->where(fn($qu) => $qu->where(function ($query) {
                    $query->whereRaw("DATEDIFF(`first_check_in_at`, NOW()) < 45 AND DATEDIFF(`first_check_in_at`, NOW()) >= 0")
                        ->orWhereRaw("DATEDIFF(`first_check_in_at`, NOW()) >= 0 AND `company_id` IS NULL");
                }));
            }
            return $q;
        })
        ->when(!$isAdmin, function ($query) {
            return $query->whereRaw("DATEDIFF(`first_check_in_at`, NOW()) >= 45");
        })
        ->get();

        return response()->json([
            'success' => true,
            'hotels' => $hotels,
            'response_time' => round(microtime(true) - LARAVEL_START, 3). "s",
        ], 200);
    }
}
