<?php

namespace App\Models\Order\Flight;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPassenger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_order_id',
        'country_id',
        'passport_issuer_country_id',
        'title',
        'first_name',
        'last_name',
        'passport_number',
        'birth_date',
        'passport_expiry_date',
    ];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'birth_date' => 'date',
        'passport_expiry_date' => 'date',
    ];

    /**
     * The attributes that should be appended to the model.
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * Get the flight order that owns the flight passenger.
     */
    public function flightOrder()
    {
        return $this->belongsTo(FlightOrder::class);
    }

    /**
     * Get the country that owns the flight passenger.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the passport issuer country that owns the flight passenger.
     */
    public function passportIssuerCountry()
    {
        return $this->belongsTo(Country::class, 'passport_issuer_country_id');
    }

    /**
     * Get the flight passenger's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
