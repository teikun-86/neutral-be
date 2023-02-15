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
            'company',
            'manifest',
            'manifest.passengers',
            'ticket',
            'payments'
        ]);

        if (!$request->user()->isAbleTo('hajj-umrah-flight-show')) {
            $reservations = $reservations->where('user_id', $request->user()->id);
        }

        if ($request->has('id')) {
            $reservations = $reservations->where('id', $request->id)->first();

            if ($reservations) {
                $reservations->append(['amount_paid']);
            }
            
            return response()->json([
                'success' => true,
                'reservation' => $reservations,
            ], 200);
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
