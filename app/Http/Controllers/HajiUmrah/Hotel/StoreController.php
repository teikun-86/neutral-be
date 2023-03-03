<?php

namespace App\Http\Controllers\HajiUmrah\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|exists:companies,id',
            'program_type' => 'required|in:9,12',
            'location_1' => 'required|string',
            'location_2' => 'required|string',
            'room_detail' => 'required|array',
            'room_detail.quad' => 'required|integer',
            'room_detail.triple' => 'required|integer',
            'room_detail.double' => 'required|integer',
            'packages_available' => 'required|integer',
            'price_per_package' => 'required|integer',
            'first_check_in_at' => 'required|date',
            'first_check_out_at' => 'required|date',
            'last_check_in_at' => 'required|date',
            'last_check_out_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $hotel = new Hotel();
            $hotel->fill($validator->validated());
            $hotel->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Hotel created successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hotel.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
