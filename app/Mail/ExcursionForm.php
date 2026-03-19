<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExcursionForm extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'EH Boutique Experience - Solicitud de información de excursión',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.excursion_form',
        );
    }

    public function attachments()
    {
        return [];
    }
}
