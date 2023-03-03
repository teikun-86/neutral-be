<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
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
        $reservations = FlightReservation::with([
            'flight',
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'flight.returnDepartureAirport',
            'flight.returnArrivalAirport',
            'company',
            'manifest',
            'manifest.passengers',
            'ticket',
            'payments',
            'user'
        ]);

        $orderBy = $request->input('order_by', 'created_at');
        $direction = $request->input('order_direction', 'desc');

        if ($request->has('id')) {
            $reservations = $reservations->where('id', $request->id)->first();

            if ($reservations) {
                $reservations->append(['amount_paid', 'amount_due', 'status']);
            }
            
            return response()->json([
                'success' => true,
                'reservation' => $reservations,
            ], 200);
        }

        if (config('app.is_admin')) {
            $reservations = $reservations->orderBy($orderBy, $direction)->with('user')->get()
                ->map(function ($res) {
                    $res->flight->append('available_seats');
                    $res->append(['amount_paid', 'amount_due', 'status']);
                    return $res;
                });
            return response()->json([
                'success' => true,
                'reservations' => $reservations
            ], 200);
        }

        if (!config('app.is_admin')) {
            $reservations = $reservations->where('user_id', $request->user()->id);
        }

        $reservations = $reservations->get()
            ->map(function($res) {
                $res->append(['amount_paid']);
                return $res;
            })
            ->sortBy('is_expired')
            ->values();

        return response()->json([
            'success' => true,
            'reservations' => $reservations,
        ], 200);
    }
}
