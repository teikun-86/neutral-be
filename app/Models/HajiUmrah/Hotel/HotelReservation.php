<?php

namespace App\Models\HajiUmrah\Hotel;

use App\Models\Company;
use App\Models\User;
use App\Traits\HasCreator;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelReservation extends Model
{
    use HasFactory, Payable, HasCreator;

    protected $table = 'haji_umrah_hotel_reservations';

    protected $fillable = [
        'hotel_id',
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

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isExpired(): Attribute
    {
        return new Attribute(
            get: fn() => $this->amount_paid > 0 ? false : $this->expired_at->isPast(),
        );
    }

    public function status(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->isExpired) return 'expired';

                if ($this->amount_paid === 0 && !$this->isExpired) return 'pending';

                if ($this->amount_paid > 0 && $this->amount_paid < $this->total_price) return 'partially paid';

                if ($this->amount_paid === $this->total_price) return 'paid';

                return 'unknown';
            },
        );
    }
}
