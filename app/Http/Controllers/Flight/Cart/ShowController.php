<?php

namespace App\Http\Controllers\Flight\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        if (!$id) {
            abort(404);
        }

        $cart = Cart::find($id);

        if (!$cart) {
            abort(404);
        }

        return response()->json([
            'success' => true,
            'cart' => $cart
        ], 200);
    }
}
