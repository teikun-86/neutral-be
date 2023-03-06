<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'phone' => ['required', 'string', 'max:255', 'unique:' . User::class . ',phone,' . $request->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',email,' . $request->id],
            'user_type' => ['required', 'in:personal,agent,company'],
            'company_id' => [Rule::requiredIf($request->user_type === 'company'), 'exists:companies,id'],
            'role_id' => ['nullable'],
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
            $user = User::where('id', $request->id)->first();
            $user->update(collect($validator->validated())->except('id')->toArray());

            $roles = $request->role_id ? [$request->role_id] : null;

            $user->detachRoles();
            
            if ($roles !== null) {
                $user->attachRoles($roles);
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
