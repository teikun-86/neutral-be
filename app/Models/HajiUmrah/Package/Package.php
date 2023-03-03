<?php

namespace App\Models\HajiUmrah\Package;

use App\Models\HajiUmrah\Flight\Flight;
use App\Models\HajiUmrah\Hotel\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'haji_umrah_packages';

    protected $fillable = [
        'flight_id',
        'hotel_id',
        'packages_available',
        'price_per_package',
        'seats_per_package',
        'program_type',
        'hotels_per_package',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class, 'flight_id', 'id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function reservations()
    {
        return $this->hasMany(PackageReservation::class, 'package_id', 'id');
    }

    public function getPackagesLeftAttribute()
    {
        return $this->packages_available - $this->reservations->where(function ($reservation) {
            return !$reservation->is_expired;
        })->sum('amount');
    }
}
