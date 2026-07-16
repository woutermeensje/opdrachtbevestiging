<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Quote $quote,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = filled($this->quote->sender_email)
            ? [new Address($this->quote->sender_email, $this->quote->sender_name)]
            : [];

        $senderCompany = $this->quote->senderCompanyDisplayName();

        return new Envelope(
            from: new Address(config('mail.from.address'), $senderCompany.' via '.config('mail.from.name')),
            replyTo: $replyTo,
            subject: 'Offerte van '.$senderCompany,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-invitation',
            text: 'emails.quote-invitation-text',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return collect($this->quote->emailAttachments())
            ->map(function (array $file): Attachment {
                $attachment = Attachment::fromStorageDisk('local', $file['path'])
                    ->as($file['name']);

                if (filled($file['mime'])) {
                    $attachment->withMime($file['mime']);
                }

                return $attachment;
            })
            ->all();
    }
}
