<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferForm extends Mailable
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
            subject: 'EH Boutique Experience - Solicitud de información de traslado',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.transfer_form',
        );
    }

    public function attachments()
    {
        return [];
    }
}
