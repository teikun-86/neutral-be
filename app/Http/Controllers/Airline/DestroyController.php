<?php

namespace App\Http\Controllers\Airline;

use App\Http\Controllers\Controller;
use App\Models\Airline;
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
            'id' => 'required|exists:airlines,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            Airline::where('id', $request->id)->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Airline deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete airline.',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
