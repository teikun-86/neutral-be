<?php

namespace App\Models;

use App\Models\HajiUmrah\Flight\Flight;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'ppiu_number',
        'image'
    ];

    /**
     * Get the flights for the company.
     */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
