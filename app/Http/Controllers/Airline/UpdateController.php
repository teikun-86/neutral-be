<?php

namespace App\Http\Controllers\Airline;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:airlines,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'code_context' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $airline = Airline::where('id', $request->id)->first();
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
    
                $name = 'airline' . time() . '.' . $file->getClientOriginalExtension();
                $path = "/airlines/$request->code";
                $file->move(storage_path("app/public/$path"), $name);
                $airline->logo = config('app.url') . "/storage/$path/$name";
            }
            $airline->name = $request->name;
            $airline->code = $request->code;
            $airline->code_context = $request->code_context;
            $airline->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'The airline has been updated.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update the airline.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
