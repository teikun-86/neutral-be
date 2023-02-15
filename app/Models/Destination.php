<?php

namespace App\Models;

use App\Traits\Viewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, Viewable;

    /**
     * The attributes that can be mass assigned.
     */
    protected $fillable = [
        'country_id',
        'province_id',
        'city_id',
        'name',
        'slug',
        'location',
        'type',
    ];

    /**
     * Get the country that owns the destination.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the province that owns the destination.
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city that owns the destination.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
