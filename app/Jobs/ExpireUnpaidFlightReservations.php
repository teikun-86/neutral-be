<?php

namespace App\Jobs;

use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireUnpaidFlightReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $expiredFlights = FlightReservation::where('expired_at', '<=', now())
            ->where('status', 'unpaid')
            ->get();

        foreach ($expiredFlights as $expiredFlight) {
            $expiredFlight->update([
                'status' => 'expired',
            ]);
        }
    }
}
