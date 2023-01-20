<?php

namespace App\Http\Controllers\Flight;

use App\BTW\BTW;
use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "DepartureAirport" => ["required"],
            "ArrivalAirport" => ["required"],
            "DepartureDate" => ["required", "date", "after_or_equal:today"],
            "ArrivalDate" => ["required", "date", "after_or_equal:DepartureDate"],
            "DirectionInd" => ["in:OneWay,Return"],
            "Adult" => ["required", "integer", "min:1"],
            "Children" => ["nullable", "integer", "min:0"],
            "Infant" => ["nullable", "integer", "min:0"],
            "Class" => ["in:Economy,Business,First"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $validator->validated();

            $btw = new BTW();
            $flights = $this->_processFlights($btw->searchFlights($query), $request->Class);

            return response()->json([
                'success' => true,
                'flights' => $flights
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "Failed to search flights. Please try again later.",
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Process the data from BTW API.
     */
    private function _processFlights($flights, string $flightClass)
    {
        $requiredIatas = [];
        $departures = collect($flights['Departure']);
        $returns = collect($flights['Return']);

        $departures->map(function($departure) use(&$requiredIatas) {
            return collect($departure)->map(function($flight) use(&$requiredIatas) {
                $requiredIatas[] = $flight['DepartureAirport'];
                $requiredIatas[] = $flight['ArrivalAirport'];
                return $flight;
            });
        });

        $returns->map(function($return) use(&$requiredIatas) {
            return collect($return)->map(function($flight) use(&$requiredIatas) {
                $requiredIatas[] = $flight['DepartureAirport'];
                $requiredIatas[] = $flight['ArrivalAirport'];
                return $flight;
            });
        });

        $requiredIatas = array_unique($requiredIatas);

        $airports = Airport::whereIn('iata', $requiredIatas)->get()->keyBy('iata')->toArray();

        $departures = $this->__applyFilter($departures, $airports, $flightClass);
        $returns = $this->__applyFilter($returns, $airports, $flightClass);

        return [
            'Departure' => $departures,
            'Return' => $returns
        ];
    }

    private function __applyFilter($flights, $airports, $flightClass)
    {
        return $flights->filter(fn($flightGroup) => count($flightGroup) === 1)->map(function ($flightGroup) use ($airports, $flightClass) {
            return collect($flightGroup)->map(function ($flight) use ($airports, $flightClass) {
                $flight['DepartureAirport'] = $airports[$flight['DepartureAirport']];
                $flight['ArrivalAirport'] = $airports[$flight['ArrivalAirport']];

                // check if the flight is available
                $flight['BookingClassAvail'] = collect($flight['BookingClassAvail'])
                        ->filter(function ($book) use ($flightClass) {
                            return $book['Class'] === $flightClass;
                        })
                        ->map(function($book) {
                            $book['Amount'] = (int) $book['Amount'];
                            return $book;
                        })
                        ->values();

                $flight['available'] = $flight['BookingClassAvail']->first();

                return $flight;
            })->filter(function ($flight) {
                return $flight['available'] !== null;
            })->map(fn($flight) => $this->__transform($flight, count($flightGroup) - 1))->values()->flatten(1);
        })->flatten(1);
    }

    private function __transform($flight, $transit = 0) {
        $res = [];

        foreach($flight['BookingClassAvail'] as $available) {
            $res[] = array_merge($flight, $available, ['BookingClassAvail' => null, 'transits' => $transit]);
        }
        
        return $res;
    }
}
