<?php

namespace App\Traits;

use App\Models\Payment\Payment;
use Illuminate\Http\UploadedFile;

trait Payable
{
    /**
     * Get the payment that owns the model.
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->with('paymentMethod');
    }

    /**
     * Get the amount paid.
     */
    public function getAmountPaidAttribute(): float|null
    {
        return $this->payments->where('status', 'paid')->sum('amount');
    }

    /**
     * Get the amount due.
     */
    public function getAmountDueAttribute(): float|null
    {
        return $this->total_price - $this->amount_paid;
    }

    /**
     * Check if the model has an unpaid payment.
     */
    public function hasUnpaidPayment(): bool
    {
        return $this->payments->where('status', '!=', 'paid')->count() > 0;
    }

    /**
     * Add payment to the model.
     */
    public function addPayment($paymentMethod, $amount, $status = 'unpaid', ?UploadedFile $paymentProof = null): Payment
    {
        return $this->payments()->create([
            'payment_method_id' => $paymentMethod->id,
            'payment_code' => $this->_generatePaymentCode(),
            'amount' => $amount,
            'status' => $status,
            'proof_of_payment' => $this->_handlePaymentProof($paymentProof)
        ]);
    }

    /**
     * Validate payment, payment proof and update payment status.
     */
    public function validatePayment($paymentCode, $paymentProof): bool
    {
        $payment = $this->getPaymentByCode($paymentCode);
        if ($payment && $payment->status === 'unpaid') {
            $payment->update([
                'status' => 'paid',
                'proof_of_payment' => $this->_handlePaymentProof($paymentProof)
            ]);
            return true;
        }
        return false;
    }


    /**
     * Get payment by payment code.
     */
    public function getPaymentByCode($paymentCode): ?Payment
    {
        return $this->payments()->where('payment_code', $paymentCode)->first();
    }
    
    /**
     * Generate payment code.
     */
    public function _generatePaymentCode(): string
    {   
        $prefix = str(str(class_basename($this)))
            ->upper()
            ->replace([
                'A', 'E', 'I', 'O', 'U'
            ], '')
            ->substr(0, 3);
        $code = $prefix . '-' . str(str()->random(12))->upper();
            
        if (Payment::where('payment_code', $code)->exists()) {
            return $this->_generatePaymentCode();
        }
        return $code;
    }

    /**
     * Handle payment proof.
     */
    public function _handlePaymentProof($paymentProof = null): ?string
    {
        // if payment proof is not null, and is a string
        if ($paymentProof && is_string($paymentProof)) {
            return $paymentProof;
        }

        // if payment proof is not null, and is an instance of UploadedFile
        if ($paymentProof && $paymentProof instanceof UploadedFile) {
            return $paymentProof->store('payment_proofs');
        }
        
        return null;
    }
}