<?php

namespace App\Http\Controllers\HajiUmrah\Package;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Package\Package;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $order = [$request->input('order_by', 'created_at'), $request->input('order_direction', 'desc')];
        $isAdmin = config('app.is_admin');
        $packages = Package::query()
            ->with([
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
                'reservations',
            ])
            ->when($isAdmin, function ($query) use ($order) {
                return $query->orderBy($order[0], $order[1]);
            });
        if ($request->has('id')) {
            $package = $packages->where('id', $request->input('id'))->first();
            $package->append([
                'packages_left'
            ]);
            $package->flight->append([
                'available_seats'
            ]);
            return response()->json([
                'success' => true,
                'package' => $package,
            ], 200);
        }

        $packages = $packages->get()->map(function($pkg) {
            $pkg->append(['packages_left']);
            $pkg->flight->append([
                'available_seats'
            ]);
            return $pkg;
        });
        return response()->json([
            'success' => true,
            'packages' => $packages
        ], 200);
    }
}
