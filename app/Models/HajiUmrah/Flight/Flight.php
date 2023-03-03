<?php

namespace App\Models\HajiUmrah\Flight;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    /**
     * The table associated with model.
     */
    protected $table = 'haji_umrah_flights';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'airline_id',
        'company_id',
        'departure_airport_id',
        'arrival_airport_id',
        'return_departure_airport_id',
        'return_arrival_airport_id',
        'price',
        'seats',
        'flight_number',
        'return_flight_number',
        'program_type',
        'depart_at',
        'arrive_at',
        'return_depart_at',
        'return_arrive_at',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'depart_at' => 'datetime',
        'arrive_at' => 'datetime',
        'return_depart_at' => 'datetime',
        'return_arrive_at' => 'datetime',
    ];

    /**
     * Get the airline that owns the flight.
     */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the company that owns the flight.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the departure airport that owns the flight.
     */
    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id', 'id');
    }

    /**
     * Get the arrival airport that owns the flight.
     */
    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id', 'id');
    }

    /**
     * Get the departure airport that owns the flight.
     */
    public function returnDepartureAirport()
    {
        return $this->belongsTo(Airport::class, 'return_departure_airport_id', 'id');
    }

    /**
     * Get the arrival airport that owns the flight.
     */
    public function returnArrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'return_arrival_airport_id', 'id');
    }

    /**
     * Get the reservations for the flight.
     */
    public function reservations()
    {
        return $this->hasMany(FlightReservation::class);
    }

    /**
     * Get the active reservations for the flight.
     */
    public function activeReservations()
    {
        return $this->reservations()->where('status', 'active');
    }

    /**
     * Get the seats that are available for the flight.
     */
    public function getAvailableSeatsAttribute()
    {
        return $this->seats - $this->reservations->where(function($reservation) {
            return !$reservation->is_expired;
        })->sum('seats');
    }
}
