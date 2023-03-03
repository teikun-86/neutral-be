<?php

namespace App\Http\Controllers\HajiUmrah\Package\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Package\PackageReservation;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isAdmin = config('app.is_admin');

        $order = [$request->input('order_by', 'created_at'), $request->input('order_direction', 'desc')];
        $reservations = PackageReservation::query()
            ->with([
                'package' => fn ($q) => $q->with([
                    'hotel' => fn ($h) => $h->with([
                        'company',
                        'reservations',
                    ]),
                    'flight' => function ($flight) {
                        return $flight->with([
                            'airline' => fn ($query) => $query->select(['id', 'name', 'code', 'logo']),
                            'departureAirport' => fn ($query) => $query->select(['id', 'name', 'iata', 'city_id', 'country_id'])->with([
                                'city' => fn ($query) => $query->select(['id', 'name', 'country_id']),
                                'country' => fn ($query) => $query->select(['id', 'name']),
                            ]),
                            'arrivalAirport' => fn ($query) => $query->select(['id', 'name', 'iata', 'city_id', 'country_id'])->with([
                                'city' => fn ($query) => $query->select(['id', 'name', 'country_id']),
                                'country' => fn ($query) => $query->select(['id', 'name']),
                            ]),
                            'returnDepartureAirport' => fn ($query) => $query->select(['id', 'name', 'iata', 'city_id', 'country_id'])->with([
                                'city' => fn ($query) => $query->select(['id', 'name', 'country_id']),
                                'country' => fn ($query) => $query->select(['id', 'name']),
                            ]),
                            'returnArrivalAirport' => fn ($query) => $query->select(['id', 'name', 'iata', 'city_id', 'country_id'])->with([
                                'city' => fn ($query) => $query->select(['id', 'name', 'country_id']),
                                'country' => fn ($query) => $query->select(['id', 'name']),
                            ]),
                            'reservations' => fn ($query) => $query->select(['id', 'flight_id', 'seats', 'expired_at']),
                            'company'
                        ]);
                    },
                ]),
                'user',
                'company'
            ])
            ->orderBy($order[0], $order[1]);

        if ($request->has('id')) {
            $reservation = $reservations->where('id', $request->input('id'))->first();
            $reservation->package->append([
                'packages_left'
            ]);
            $reservation->package->flight->append([
                'available_seats'
            ]);
            $reservation->append([
                'is_expired',
                'status',
                'amount_paid',
                'amount_due'
            ]);
            return response()->json([
                'success' => true,
                'reservation' => $reservation,
            ], 200);
        }

        $reservations = $reservations->get()->map(function ($reservation) {
            $reservation->package->append([
                'packages_left'
            ]);
            $reservation->package->flight->append([
                'available_seats'
            ]);
            $reservation->append([
                'is_expired',
                'status',
                'amount_paid',
                'amount_due'
            ]);
            return $reservation;
        });

        return response()->json([
            'success' => true,
            'reservations' => $reservations,
        ], 200);
    }
}
