<?php

namespace App\Models\Order\Flight;

use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnFlight extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_order_id',
        'departure_airport_id',
        'arrival_airport_id',
        'airline_id',
        'class',
        'flight_number',
        'duration',
        'price',
        'currency_code',
        'class_code',
        'departure_at',
        'arrival_at',
    ];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
    ];

    /**
     * Get the flight order that owns the departure flight.
     */
    public function flightOrder()
    {
        return $this->belongsTo(FlightOrder::class);
    }

    /**
     * Get the departure airport that owns the departure flight.
     */
    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    /**
     * Get the arrival airport that owns the departure flight.
     */
    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    /**
     * Get the airline that owns the departure flight.
     */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the Transit Flights.
     */
    public function transitFlights()
    {
        return $this->hasMany(TransitFlight::class);
    }
}
