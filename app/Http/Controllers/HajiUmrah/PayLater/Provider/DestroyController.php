<?php

namespace App\Http\Controllers\HajiUmrah\PayLater\Provider;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PaylaterProvider;
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
            'id' => 'required|exists:paylater_providers,id',
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
            $provider = PaylaterProvider::where('id', $request->id)->first();

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'paylater.provider.not_found',
                    'errors' => [
                        'id' => 'paylater.provider.not_found',
                    ]
                ], 422);
            }

            $provider->delete();
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'paylater.provider.deleted',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'paylater.provider.delete_failed',
            ], 500);
        }
    }
}
