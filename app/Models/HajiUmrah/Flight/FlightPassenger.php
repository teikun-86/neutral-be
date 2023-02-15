<?php

namespace App\Models\HajiUmrah\Flight;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPassenger extends Model
{
    use HasFactory;

    /**
     * The table associated with model.
     */
    protected $table = 'haji_umrah_flight_passengers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_manifest_id',
        'name',
        'passport_number',
        'visa_number',
        'date_of_birth',
        'gender',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'datetime',
    ];

    /**
     * Get the manifest that owns the passenger.
     */
    public function manifest()
    {
        return $this->belongsTo(FlightManifest::class, 'flight_manifest_id', 'id');
    }
}
