<?php

namespace App\Services;

use App\Mail\ErrorReportMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class ErrorReportMailer
{
    /**
     * Mailt een onverwachte fout naar de beheerder. Dezelfde fout wordt
     * hooguit één keer per kwartier gemaild, zodat een storing geen
     * mailstorm veroorzaakt.
     */
    public function report(Throwable $exception): void
    {
        $recipient = config('mail.admin_address');

        if (! filled($recipient) || ! app()->environment('production')) {
            return;
        }

        try {
            if ($exception instanceof FatalError) {
                // Na een geheugenfout is er geen ruimte meer om de mail op te bouwen.
                @ini_set('memory_limit', '-1');
            }

            // Bewust de file-cache: de database kan zelf de oorzaak van de fout zijn.
            $key = 'error-report:'.sha1($exception::class.'|'.$exception->getFile().'|'.$exception->getLine());

            if (! Cache::store('file')->add($key, true, now()->addMinutes(15))) {
                return;
            }

            $request = app()->runningInConsole() ? null : request();

            Mail::to($recipient)->send(new ErrorReportMail(
                exceptionClass: $exception::class,
                exceptionMessage: $exception->getMessage(),
                location: $exception->getFile().':'.$exception->getLine(),
                trace: mb_substr($exception->getTraceAsString(), 0, 8000),
                url: $request ? $request->method().' '.$request->fullUrl() : 'console: '.implode(' ', $_SERVER['argv'] ?? []),
                user: $request?->user() ? $request->user()->id.' - '.$request->user()->email : null,
                occurredAt: now()->format('d-m-Y H:i:s'),
            ));
        } catch (Throwable) {
            // Een mislukte foutmail mag de oorspronkelijke foutafhandeling niet verstoren.
        }
    }
}
