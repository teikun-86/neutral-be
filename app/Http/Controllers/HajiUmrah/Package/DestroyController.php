<?php

namespace App\Http\Controllers\HajiUmrah\Package;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Package\Package;
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
            'id.*' => 'required|exists:haji_umrah_packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $packages = Package::whereIn('id', $request->ids)->get();
            foreach ($packages as $package) {
                $package->reservations()->delete();
                $package->delete();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Packages deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete packages.',
                'errors' => $th->getMessage(),
            ], 500);    
        }
    }
}
