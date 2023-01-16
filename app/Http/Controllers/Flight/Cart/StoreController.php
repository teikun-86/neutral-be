<?php

namespace App\Http\Controllers\Flight\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

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
        $data = $request->all();

        $cart = new Cart();
        $cart->fill([
            'type' => 'flight',
            'items' => $data,
            'step' => 'checkout'
        ]);

        $cart->save();

        return response()->json([
            'success' => true,
            'cart' => $cart
        ], 200);
    }
}
