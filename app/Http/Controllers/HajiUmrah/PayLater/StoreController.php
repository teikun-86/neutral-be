<?php

namespace App\Http\Controllers\HajiUmrah\PayLater;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PayLater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            'provider_id' => 'required|exists:paylater_providers,id',
            'identity_image' => 'required|image',
            'npwp_image' => 'required|image',
            'occupation' => 'required|string',
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
            $user = $request->user();

            $files = $this->_processFiles($request);

            $payLater = PayLater::create([
                'user_id' => $user->id,
                'paylater_provider_id' => $request->provider_id,
                'identity_image' => $files['identity_image'],
                'npwp_image' => $files['npwp_image'],
                'occupation' => $request->occupation,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'paylater.stored',
                'data' => $payLater,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'paylater.store_failed',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Process the files.
     */
    private function _processFiles(Request $request)
    {
        $user = $request->user();
        $identity = $request->file('identity_image');
        $npwp = $request->file('npwp_image');

        $ts = now()->timestamp;

        $identityFilename = "ID-{$user->id}{$ts}.{$identity->extension()}";
        $npwpFilename = "NPWP-{$user->id}{$ts}.{$npwp->extension()}";
        $paths = [
            'id' => 'paylater/identity',
            'npwp' => 'paylater/npwp',
        ];

        $identity->move(storage_path("app/public/{$paths['id']}"), $identityFilename);
        $npwp->move(storage_path("app/public/{$paths['npwp']}"), $npwpFilename);
        
        return [
            'identity_image' => asset("storage/{$paths['id']}/{$identityFilename}"),
            'npwp_image' => asset("storage/{$paths['npwp']}/{$npwpFilename}"),
        ];
    }
}
