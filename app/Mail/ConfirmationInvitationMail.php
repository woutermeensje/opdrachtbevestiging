<?php

namespace App\Mail;

use App\Models\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Confirmation $confirmation,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Opdrachtbevestiging '.$this->confirmation->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-invitation',
        );
    }
}
