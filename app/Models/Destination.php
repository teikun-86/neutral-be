<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var string[]
     */
    protected $fillable = [
        'country_id',
        'city_id',
        'district_id',
        'type',
        'name',
        'slug',
        'description',
        'address',
        'image',
        'price',
    ];

    /**
     * The attributes that should be casted to native types.
     * 
     * @var array
     */
    protected $casts = [
        'image' => 'array',
    ];

    /**
     * Get the country that owns the Destination
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the city that owns the Destination
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the district that owns the Destination
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
