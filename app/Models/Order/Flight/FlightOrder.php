<?php

namespace App\Models\Order\Flight;

use App\Models\Airport;
use App\Models\Order\Order;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightOrder extends Model
{
    use HasFactory, Payable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'departure_airport_id',
        'arrival_airport_id',
        'book_at',
        'trip_type',
        'total_price',
    ];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'book_at' => 'datetime',
    ];

    /**
     * Get the order that owns the flight order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the departure airport that owns the flight order.
     */
    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    /**
     * Get the arrival airport that owns the flight order.
     */
    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    /**
     * Get the flight passengers.
     */
    public function passengers()
    {
        return $this->hasMany(FlightPassenger::class);
    }

    /**
     * Get the flight addons.
     */
    public function addons()
    {
        return $this->hasMany(FlightAddon::class);
    }

    /**
     * Get the departure flight.
     */
    public function departureFlight()
    {
        return $this->hasOne(DepartureFlight::class)->with([
            'departureAirport', 'arrivalAirport', 'airline', 'transitFlights'
        ]);
    }

    /**
     * Get the return flight.
     */
    public function returnFlight()
    {
        return $this->hasOne(ReturnFlight::class)->with([
            'departureAirport', 'arrivalAirport', 'airline', 'transitFlights'
        ]);
    }
}
