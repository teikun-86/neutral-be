<?php

namespace App\Http\Controllers\HajiUmrah\Visa;

use App\Http\Controllers\Controller;
use App\Imports\HajiUmrah\VisaImport;
use App\Models\HajiUmrah\Visa\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StoreController extends Controller
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
            'company_id' => 'required|exists:companies,id',
            'visa_type' => 'required|string',
            'flight_code' => 'required|string',
            'file' => 'required|file|mimes:xlsx,xls,csv',
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
            $file = $request->file('file');
            $path = "visa/{$request->company_id}";
            $ts = now()->timestamp;
            $filename = "VISA-{$request->company_id}{$ts}.{$file->getClientOriginalExtension()}";
            $file = $file->move(storage_path("app/public/$path"), $filename);
            
            $visa = VisaApplication::create([
                'company_id' => $request->company_id,
                'user_id' => $request->user()->id,
                'visa_type' => $request->visa_type,
                'flight_code' => $request->flight_code,
                'file_path' => asset("storage/$path/{$filename}"),
                'status' => 'pending'
            ]);

            Excel::import(new VisaImport($visa), $file);
            
            $visa = $visa->load('applicants');
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'visa.created',
                'data' => $visa
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'visa.store.failed',
                'errors' => $th->getMessage(),
                'trace' => $th->getTrace()
            ], 500);
        }
    }
}
