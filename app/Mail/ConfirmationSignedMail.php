<?php

namespace App\Mail;

use App\Models\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Confirmation $confirmation,
        public readonly bool $forClient,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = filled($this->confirmation->sender_email)
            ? [new Address($this->confirmation->sender_email, $this->confirmation->sender_name)]
            : [];

        $senderCompany = $this->confirmation->senderCompanyDisplayName();

        return new Envelope(
            from: new Address(config('mail.from.address'), $senderCompany.' via '.config('mail.from.name')),
            replyTo: $replyTo,
            subject: 'Opdrachtbevestiging '.$this->confirmation->reference.' is getekend',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-signed',
            text: 'emails.confirmation-signed-text',
            with: [
                'forClient' => $this->forClient,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->confirmation->hasPdf()) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->confirmation->pdf_path)
                ->as($this->confirmation->pdf_original_name ?: $this->confirmation->pdfDownloadName())
                ->withMime($this->confirmation->pdf_mime_type ?: 'application/pdf'),
        ];
    }
}
