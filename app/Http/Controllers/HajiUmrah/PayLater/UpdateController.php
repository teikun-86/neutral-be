<?php

namespace App\Http\Controllers\HajiUmrah\PayLater;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\PayLater\PayLater;
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
            'id' => 'required|exists:pay_laters,id',
            'status' => 'required|in:pending,approved,rejected',
            'provider_id' => 'required|exists:paylater_providers,id',
            'identity_image' => 'nullable|image',
            'npwp_image' => 'nullable|image',
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

            $files = $this->_processFiles($request);

            $payLater->update([
                'provider_id' => $request->provider_id,
                'identity_image' => isset($files['identity_image'])
                                    ? $files['identity_image']
                                    : $payLater->identity_image,
                'npwp_image' => isset($files['npwp_image']) 
                                    ? $files['npwp_image']
                                    : $payLater->npwp_image,
                'occupation' => $request->occupation,
                'status' => $request->status,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'paylater.updated',
                'data' => $payLater,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'paylater.update_failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Process the files.
     */
    private function _processFiles(Request $request)
    {
        $user = $request->user();
        $files = [];

        $ts = now()->timestamp;
        $paths = [
            'id' => 'paylater/identity',
            'npwp' => 'paylater/npwp',
        ];

        if ($request->hasFile('identity_image')) {
            $identity = $request->file('identity_image');
            $identityFilename = "ID-{$user->id}{$ts}.{$identity->extension()}";
            $identity->move(storage_path("app/public/{$paths['id']}"), $identityFilename);
            $files['identity_image'] = asset("storage/{$paths['id']}/{$identityFilename}");
        }

        if ($request->hasFile('npwp_image')) {
            $npwp = $request->file('npwp_image');
            $npwpFilename = "NPWP-{$user->id}{$ts}.{$npwp->extension()}";
            $npwp->move(storage_path("app/public/{$paths['npwp']}"), $npwpFilename);        
            $files['npwp_image'] = asset("storage/{$paths['npwp']}/{$npwpFilename}");
        }
        

        return $files;
    }
}
