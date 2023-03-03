<?php

namespace App\Http\Controllers\HajiUmrah\Hotel\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Hotel\HotelReservation;
use App\Models\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddPaymentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:haji_umrah_hotel_reservations,id',
            'amount' => 'required|integer',
            'payment_method_code' => 'required|exists:payment_methods,code',
        ]);

        $isAdmin = config('app.is_admin');

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $reservation = HotelReservation::with('payments')->where('id', $request->id)->first();
            $unpaid = $reservation->amount_due;
            $amount = $request->amount;
            
            if ($amount > $unpaid) {
                return response()->json([
                    'success' => false,
                    'message' => 'The amount is greater than the amount due.',
                    'errors' => [
                        'amount' => [
                            "The amount due is Rp ". number_format($unpaid, 0, ',', '.')
                        ]
                    ]
                ], 422);
            }

            $reservation->addPayment(
                paymentMethod: PaymentMethod::where('code', $request->payment_method_code)->first(),
                amount: $amount,
                status: $isAdmin ? 'paid' : "unpaid",
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment.',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
