<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class confirmReservationMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $data, $subject, $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
        $reservation_number = $data['reservation_number'];
        $this->subject = "Confirmacion reserva N° $reservation_number - EH Boutique Experience";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = isset($this->data['agency_type'])
            ? 'emails.agencyConfirmReservation'
            : 'emails.confirmReservation';

        return $this->subject($this->subject)
                    ->view($view);
    }
}
