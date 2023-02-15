<?php

namespace App\Http\Controllers\Flight;

use App\Services\BTW;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrebookController extends Controller
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
            'DirectionInd' => ["required", "string", "in:OneWay,Return"],
            'DepartureAirport' => ["required", "string", "exists:airports,iata"],
            'ArrivalAirport' => ["required", "string", "exists:airports,iata"],
            'DepartureDateTime' => ["required", "date", "after_or_equal:today"],
            'ArrivalDateTime' => ["required", "date", "after:DepartureDateTime"],
            'StopQuantity' => ["required", "integer", "min:0"],
            'FlightNumber' => ["required"],
            'RPH' => ["required", "string"],
            'ResBookDesigCode' => ["required", "string"],
            'NumberInParty' => ["required", "integer", "min:1"],
            'Code' => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // DB::beginTransaction();
            $data = $validator->validated();
            $prebook = (new BTW)->preBook($data);
            // DB::commit();
            return response()->json([
                'success' => true,
                'prebook' => $prebook
            ], 200);
        } catch (\Throwable $th) {
            // DB::rollBack();
            throw $th;
        }
    }
}
