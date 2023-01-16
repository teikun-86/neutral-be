<?php

namespace App\Http\Controllers\Flight\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrebookController extends Controller
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
            'id' => ["required", "exists:carts,id"],
            'contact.title' => ["required", "string", "in:Mr,Mrs,Ms"],
            'contact.name' => ["required", "string"],
            'contact.email' => ["required", "email"],
            'contact.phone' => ["required", "string"],
            'contact.countryCode.*' => ["required", "string"],
            'passengers.*.title' => ["required", "string", "in:Mr,Mrs,Ms"],
            'passengers.*.name' => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
                $cart = Cart::whereId($request->id)->first();
                $cart->update([
                    'contact' => $request->contact,
                    'passengers' => $request->passengers,
                    'step' => 'prebook'
                ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cart prebooked successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to prebook your order. Please try again later.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
