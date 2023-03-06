<?php

namespace App\Models\HajiUmrah\Package;

use App\Models\Company;
use App\Models\HajiUmrah\Flight\FlightManifest;
use App\Models\User;
use App\Traits\HasCreator;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageReservation extends Model
{
    use HasFactory, HasCreator, Payable;

    protected $table = 'haji_umrah_package_reservations';

    protected $fillable = [
        'package_id',
        'user_id',
        'company_id',
        'amount',
        'price_per_package',
        'total_price',
        'guests_map',
        'expired_at',
        'reserved_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function isExpired(): Attribute
    {
        return new Attribute(
            get: fn () => $this->amount_paid > 0 ? false : $this->expired_at->isPast(),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->isExpired) return 'expired';

                if ($this->amount_paid === 0 && !$this->isExpired) return 'pending';

                if ($this->amount_paid > 0 && $this->amount_paid < $this->total_price) return 'partially paid';

                if ($this->amount_paid === $this->total_price) return 'paid';

                return 'unknown';
            },
        );
    }

    public function manifest()
    {
        return $this->hasOne(FlightManifest::class, 'package_reservation_id', 'id');
    }
}
