<?php

namespace App\Models\HajiUmrah\Hotel;

use App\Models\Company;
use App\Models\HajiUmrah\Package\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'haji_umrah_hotels';

    protected $fillable = [
        'company_id',
        'program_type',
        'location_1',
        'location_2',
        'room_detail',
        'packages_available',
        'price_per_package',
        'first_check_in_at',
        'first_check_out_at',
        'last_check_in_at',
        'last_check_out_at',
    ];

    protected $casts = [
        'room_detail' => 'array',
        'first_check_in_at' => 'datetime',
        'first_check_out_at' => 'datetime',
        'last_check_in_at' => 'datetime',
        'last_check_out_at' => 'datetime',
    ];

    protected $appends = [
        'packages_left'
    ];

    public function packages()
    {
        return $this->hasMany(Package::class, 'hotel_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function reservations()
    {
        return $this->hasMany(HotelReservation::class, 'hotel_id', 'id');
    }

    public function getPackagesLeftAttribute()
    {
        return $this->packages_available - $this->reservations->where(function ($reservation) {
            return !$reservation->is_expired;
        })->sum('amount');
    }
}
