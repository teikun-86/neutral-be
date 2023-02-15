<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'image',
        'enabled'
    ];

    /**
     * The attributes that should be casted to native attributes.
     */
    protected $casts = [
        'enabled' => 'boolean'
    ];

    /**
     * Get the payments for the payment method.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
