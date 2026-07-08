<?php

namespace App\Mail;

use App\Models\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationRetractionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Confirmation $confirmation,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = filled($this->confirmation->sender_email)
            ? [new Address($this->confirmation->sender_email, $this->confirmation->sender_name)]
            : [];

        return new Envelope(
            replyTo: $replyTo,
            subject: 'Opdrachtbevestiging '.$this->confirmation->reference.' is ingetrokken',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-retraction',
            text: 'emails.confirmation-retraction-text',
        );
    }
}
