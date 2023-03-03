<?php

namespace App\Http\Controllers\HajiUmrah\Flight;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:haji_umrah_flights,id',
            'company_id' => 'nullable',
            'airline_id' => 'required|exists:airlines,id',
            'flight_number' => 'nullable',
            'return_flight_number' => 'nullable',
            'departure_airport_id' => 'required|exists:airports,id',
            'arrival_airport_id' => 'required|exists:airports,id',
            'return_departure_airport_id' => 'required|exists:airports,id',
            'return_arrival_airport_id' => 'required|exists:airports,id',
            'program_type' => 'required|in:9,12',
            'price' => 'required|numeric',
            'seats' => 'required|numeric',
            'depart_at' => 'required|date_format:Y-m-d H:i',
            'arrive_at' => 'required|date_format:Y-m-d H:i',
            'return_depart_at' => 'required|date_format:Y-m-d H:i',
            'return_arrive_at' => 'required|date_format:Y-m-d H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 422);

        }

        try {
            DB::beginTransaction();

            $flight = Flight::find($request->id);
            if (!$flight) {
                $exc = new ModelNotFoundException();
                $exc->setModel(Flight::class, $request->id);
                throw $exc;
            }

            $flight->fill(
                collect($validator->validated())->except('id')->toArray()
            );

            $flight->company_id = $request->company_id;

            $flight->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flight Updated',
                'data' => $flight
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'No flight with the given id was found'
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update a flight',
            ], 500);
        }
    }
}
