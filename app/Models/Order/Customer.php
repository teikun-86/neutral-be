<?php

namespace App\Models\Order;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'country_id',
        'title',
        'name',
        'email',
        'phone',
    ];

    /**
     * Get the order that owns the customer.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the country that owns the customer.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
