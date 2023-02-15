<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var string[]
     */
    protected $fillable = [
        'name',
        'country_id'
    ];

    /**
     * Get the country that owns the city.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the tours for the city.
     */
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    /**
     * Get the districts for the city.
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
