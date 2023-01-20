<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'dial_code',
        'flag'
    ];

    /**
     * Get the cities for the country.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
