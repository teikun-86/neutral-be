<?php

namespace App\Http\Controllers\HajiUmrah\Flight;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $orderBy = $request->input('order_by', 'depart_at');
        $direction = $request->input('order_direction', 'asc');
        $withExpired = $request->boolean('with_expired', false);
        $flights = Flight::query()
            ->when(!config('app.is_admin'), function($query) use($user, $orderBy, $direction) {
                return $query->where('company_id', $user->company->id)
                    ->orderBy($orderBy, $direction);
            })
            ->when(!$withExpired, function($query) {
                return $query->whereRaw("DATEDIFF(`depart_at`, NOW()) >= 45");
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
                'reservations' => fn ($query) => $query->select(['id', 'flight_id', 'seats', 'expired_at']),
                'company'
            ])
            ->get()
            ->map(function($flight) {
                $flight->setHidden([
                    ...$flight->getHidden(),
                    'reservations'
                ]);
                $flight->append(['available_seats']);
                return $flight;
            });

        return response()->json([
            'success' => true,
            'flights' => $flights,
        ], 200);
    }
}
