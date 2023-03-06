<?php

namespace App\Http\Controllers\HajiUmrah\Flight\Reservation;

use App\Http\Controllers\Controller;
use App\Models\HajiUmrah\Flight\FlightReservation;
use App\Models\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddPaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $paymentMethods = PaymentMethod::get();
        
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:haji_umrah_flight_reservations,id',
            'payment_method_code' => ['required', 'in:' . implode(',', $paymentMethods->pluck('code')->toArray())],
            'amount' => 'required|integer|min:1',
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
                $reservation = FlightReservation::with(['flight'])
                    ->where('id', $request->id)
                    ->where('user_id', $request->user()->id)
                    ->first();

                if (!$reservation) {
                    throw new \Exception('reservation.not_found');
                }

                if ($reservation->is_expired) {
                    throw new \Exception('reservation.expired');
                }

                if ($reservation->status == 'paid') {
                    throw new \Exception('reservation.already_paid');
                }

                $paymentMethod = $paymentMethods->where('code', $request->payment_method_code)->first();

                $payment = $reservation->addPayment(
                    $paymentMethod,
                    $request->amount,
                    config('app.is_admin') ? 'paid' : 'unpaid'
                );
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'payment.added',
                'data' => $payment,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'payment.failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
