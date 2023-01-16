<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CredentialsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone,' . $user->id],
        ]);

        try {
            DB::beginTransaction();
            
            $toUpdate = [];
            if ($request->email !== $user->email) {
                $toUpdate['email'] = $request->email;
            }

            if ($request->phone !== $user->phone) {
                $toUpdate['phone'] = $request->phone;
            }
            
            if (isset($toUpdate['email'])) {
                $toUpdate['email_verified_at'] = null;
            }
            $user->update($toUpdate);

            if (isset($toUpdate['email'])) {
                $user->sendEmailVerificationNotification();
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Credentials updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Credentials update failed'
            ], 500);
        }
    }
}
