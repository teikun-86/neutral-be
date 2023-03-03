<?php

namespace App\Http\Controllers\HajiUmrah\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:haji_umrah_hotels,id'
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
            $hotels = Hotel::whereIn('id', $request->ids)->get();
            if (!$hotels) {
                return response()->json([
                    'success' => false,
                    'message' => "No hotel with the given ids found.",
                ], 400);
            }
            $hotels->each(function ($hotel) {
                $hotel->reservations()->delete();
                $hotel->packages()->delete();
                $hotel->delete();
            });
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Hotel deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hotel.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
