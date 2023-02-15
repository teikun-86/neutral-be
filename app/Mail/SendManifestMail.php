<?php

namespace App\Mail;

use App\Models\HajiUmrah\Flight\FlightReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendManifestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The FlightReservation instance.
     */
    public FlightReservation $flightReservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(FlightReservation $flightReservation)
    {
        $this->flightReservation = $flightReservation;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Flight Reservation Manifest - Reservation ID: ' . $this->flightReservation->id
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'mail.send-manifest-mail',
            with: [
                'flightReservation' => $this->flightReservation,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $reservation = $this->flightReservation;
        
        $basename = basename($reservation->manifest->manifest_file);
        
        return [
            Attachment::fromStorage("public/manifest/{$reservation->id}/{$basename}")
                ->as($basename)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ];
    }
}
