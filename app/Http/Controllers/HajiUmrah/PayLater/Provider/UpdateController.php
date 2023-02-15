<?php

namespace App\Http\Controllers\HajiUmrah\PayLater\Provider;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PaylaterProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
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
            'name' => 'required|string',
            'code' => 'required|string|unique:paylater_providers,code,' . $request->id . ',id',
            'logo' => 'nullable|image',
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
            $provider->fill([
                'name' => $request->name,
                'code' => $request->code
            ]);
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $ts = now()->timestamp;
                $filename = "LG-{$ts}.{$logo->extension()}";
    
                $path = "paylater/providers";
                $logo->move(storage_path("app/public/$path"), $filename);
                $provider->image = asset("storage/$path/{$filename}");
            }
            $provider->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'paylater.provider.updated',
                'data' => $provider,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'paylater.provider.update_failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
