<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_method_id',
        'payable_id',
        'payable_type',
        'payment_code',
        'amount',
        'status',
        'proof_of_payment',
    ];

    /**
     * Get the payment method that owns the payment.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the payable that owns the payment.
     */
    public function payable()
    {
        return $this->morphTo();
    }
}
