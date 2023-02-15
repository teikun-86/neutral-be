<?php

namespace App\Models\HajiUmrah\Flight;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightManifest extends Model
{
    use HasFactory;

    /**
     * The table associated with model.
     */
    protected $table = 'haji_umrah_flight_manifest';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_reservation_id',
        'user_id',
        'manifest_file',
        'status',
    ];

    /**
     * Get the reservation that owns the manifest.
     */
    public function reservation()
    {
        return $this->belongsTo(FlightReservation::class, 'flight_reservation_id');
    }

    /**
     * Get the user that owns the manifest.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the passengers for the manifest.
     */
    public function passengers()
    {
        return $this->hasMany(FlightPassenger::class, 'flight_manifest_id', 'id');
    }
}
