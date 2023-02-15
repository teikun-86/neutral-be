<?php

namespace App\Http\Controllers\HajiUmrah\Visa;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Visa\VisaApplication;
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
            'id' => 'required|exists:visa_applications,id',
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
            $visa = VisaApplication::where([
                'id' => $request->id,
            ])->first();

            if (!$visa) {
                return response()->json([
                    'success' => false,
                    'message' => 'visa.not_found',
                    'errors' => [
                        'id' => 'visa.not_found',
                    ]
                ], 422);
            }
            $visa->applicants()->delete();
            $visa->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'visa.deleted',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'visa.delete_failed',
            ], 500);
        }
    }
}
