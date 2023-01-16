<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'image'],
        ]);

        try {
            DB::beginTransaction();
            $toUpdate = [
                'name' => $request->name,
            ];
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = "avatars/{$request->user()->id}";
                $filename = md5($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path("app/public/$path"), $filename);
                $toUpdate['avatar'] = config('app.url') . "/storage/$path/$filename";
            }
            $request->user()->update($toUpdate);
            DB::commit();
            return response()->noContent();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
            ], 500);
        }
    }
}
