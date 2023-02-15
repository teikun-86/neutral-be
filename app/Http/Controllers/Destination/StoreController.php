<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $this->validate($request, [
            'country' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'photo.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            DB::beginTransaction();
            $photos = [];
            foreach ($request->file('photo') as $photo) {
                $path = $photo->store('public/destination');
                $photos[] = $path;
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            // throw $th;
        }
    }
}
