<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\HotelReservation;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isAdmin = config('app.is_admin');
        $user = $request->user();
        $order = [$request->input('order_by', 'created_at'), $request->input('order_direction', 'desc')];
        $reservations = HotelReservation::query()
        ->when($isAdmin, function($query) use ($order) {
            return $query->orderBy($order[0], $order[1]);
        }, function($query) use($user) {
            return $query->where('user_id', $user->id);
        })
        ->with([
            'hotel',
            'hotel.company',
            'company',
            'user'
        ]);

        if ($request->has('id')) {
            $reservations = $reservations->where('id', $request->id)->first();
            if ($reservations) {
                $reservations->append(['is_expired', 'amount_paid', 'amount_due', 'status']);
            }
            return response()->json([
                'success' => true,
                'reservation' => $reservations,
            ], 200);
        }
        
        $reservations = $reservations
        ->get()
        ->map(function($res) {
            $res->append(['is_expired', 'amount_paid', 'amount_due', 'status']);
            return $res;
        });
        
        if (!$isAdmin) {
            $reservations = $reservations->sortBy('is_expired')->values();
        }
        
        return response()->json([
            'success' => true,
            'reservations' => $reservations,
        ], 200);
    }
}
