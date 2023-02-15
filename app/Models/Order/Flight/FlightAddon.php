<?php

namespace App\Models\Order\Flight;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightAddon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_order_id',
        'name',
        'description',
        'price_per_passenger',
        'total_price',
    ];

    /**
     * Get the flight order that owns the flight addon.
     */
    public function flightOrder()
    {
        return $this->belongsTo(FlightOrder::class);
    }
}
