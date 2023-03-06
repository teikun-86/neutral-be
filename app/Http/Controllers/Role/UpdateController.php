<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
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
            'id' => 'required|exists:roles,id',
            'name' => 'required|unique:roles,name,' . $request->id,
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $role = Role::where('id', $request->id)->first();
            $role->update([
                'display_name' => $request->name,
                'name' => str($request->name)->snake(),
                'description' => $request->description
            ]);
            $role->syncPermissions($request->permissions);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
