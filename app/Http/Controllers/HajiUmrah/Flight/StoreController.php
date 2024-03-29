<?php

namespace App\Http\Controllers\HajiUmrah\Flight;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|exists:companies,id',
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
            $flight = new Flight();
            $flight->fill($validator->validated());
            $flight->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Flight Stored'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to store a flight',
                'errors' => $th->getMessage()
            ], 500);
        }
        
    }
}
