<?php

namespace App\Models\Order;

use App\Models\Order\Flight\FlightOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'order_type',
        'status',
        'book_code',
        'expired_at',
    ];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'expired_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flight order details.
     */
    public function flightOrder()
    {
        return $this->hasOne(FlightOrder::class);
    }

    /**
     * Get the customer who made the order.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
