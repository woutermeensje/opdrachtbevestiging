<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

class ErrorReportMail extends Mailable
{
    public function __construct(
        public readonly string $exceptionClass,
        public readonly string $exceptionMessage,
        public readonly string $location,
        public readonly string $trace,
        public readonly string $url,
        public readonly ?string $user,
        public readonly string $occurredAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.config('app.name').'] Fout: '.Str::limit(class_basename($this->exceptionClass).' - '.$this->exceptionMessage, 150),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.error-report-text',
        );
    }
}
