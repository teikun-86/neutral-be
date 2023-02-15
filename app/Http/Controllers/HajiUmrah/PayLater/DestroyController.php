<?php

namespace App\Http\Controllers\HajiUmrah\PayLater;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PayLater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestroyController extends Controller
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
            'id' => 'required|exists:pay_laters,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validation.failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $payLater = PayLater::where([
                'id' => $request->id,
            ])->first();

            if (!$payLater) {
                return response()->json([
                    'success' => false,
                    'message' => 'paylater.not_found',
                    'errors' => [
                        'id' => 'paylater.not_found',
                    ]
                ], 422);
            }

            $payLater->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'paylater.deleted',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'paylater.delete_failed',
            ], 500);
        }
    }
}
