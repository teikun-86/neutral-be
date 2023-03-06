<?php

namespace App\Http\Controllers\HajiUmrah\Flight;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\Request;

class PoolController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $flights = Flight::query()
            ->where(function($query) {
                $query->whereRaw("DATEDIFF(`depart_at`, NOW()) < 45 AND DATEDIFF(`depart_at`, NOW()) >= 0")
                    ->orWhereRaw("DATEDIFF(`depart_at`, NOW()) >= 0 AND `company_id` IS NULL");
            })
            ->with([
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
                'reservations',
                'company'
            ])
            ->get()
            ->map(function($flight) {
                $flight->append('available_seats');
                $flight->setHidden([
                    ...$flight->getHidden(),
                    'reservations',
                    'company'
                ]);
                return $flight;
            });

        return response()->json([
            'success' => true,
            'flights' => $flights,
        ], 200);
    }
}
