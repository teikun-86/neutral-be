<?php

namespace App\Models\HajiUmrah\Flight;

use App\Models\Company;
use App\Models\User;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightReservation extends Model
{
    use HasFactory, Payable;

    /**
     * The table associated with model.
     */
    protected $table = 'haji_umrah_flight_reservations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_id',
        'user_id',
        'company_id',
        'price_per_seat',
        'total_price',
        'seats',
        'status',
        'expired_at',
        'paid_at',
        'reserved_at',
        'pool'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'reserved_at' => 'datetime',
        'pool' => 'boolean'
    ];

    /**
     * The attributes that should be appended.
     */
    protected $appends = [
        'is_expired',
    ];

    /**
     * Get the flight that owns the reservation.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class); 
    }

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the reservation.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the manifest for the reservation.
     */
    public function manifest()
    {
        return $this->hasOne(FlightManifest::class, 'flight_reservation_id', 'id');
    }

    /**
     * Get the ticket for the reservation.
     */
    public function ticket()
    {
        return $this->hasOne(FlightTicket::class, 'flight_reservation_id', 'id');
    }

    /**
     * Check if the reservation is expired.
     */
    public function isExpired(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->amount_paid > 0) return false;
                return $this->expired_at->isPast();
            },
        ); 
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
}
