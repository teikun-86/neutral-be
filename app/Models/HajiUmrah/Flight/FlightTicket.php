<?php

namespace App\Models\HajiUmrah\Flight;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightTicket extends Model
{
    use HasFactory;

    /**
     * The table associated with model.
     */
    protected $table = 'haji_umrah_flight_tickets';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_reservation_id',
        'ticket_path',
    ];

    /**
     * Get the reservation that owns the ticket.
     */
    public function reservation()
    {
        return $this->belongsTo(FlightReservation::class, 'flight_reservation_id');
    }
}
