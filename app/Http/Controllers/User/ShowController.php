<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $order = [$request->input('order_by', 'name'), $request->input('order_direction', 'asc')];
        return response()->json([
            'success' => true,
            'users' => User::with(['company', 'roles', 'country'])->orderBy($order[0], $order[1])->get()
        ], 200);
    }
}
