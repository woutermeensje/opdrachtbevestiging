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

class ConfirmationInvitationMail extends Mailable
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
            subject: 'Opdrachtbevestiging '.$this->confirmation->reference.': '.$this->confirmation->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-invitation',
            text: 'emails.confirmation-invitation-text',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return collect($this->confirmation->emailAttachments())
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
