<?php

namespace Tests\Feature;

use App\Mail\ErrorReportMail;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ErrorReportMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.admin_address' => 'admin@example.test',
            'cache.stores.file.path' => sys_get_temp_dir().'/error-report-test-'.uniqid(),
        ]);
    }

    public function test_unexpected_error_is_mailed_to_admin_once_per_interval(): void
    {
        Mail::fake();
        $this->app['env'] = 'production';

        $exception = new RuntimeException('PDF kon niet worden gemaakt');

        $this->app->make(ExceptionHandler::class)->report($exception);
        $this->app->make(ExceptionHandler::class)->report($exception);

        Mail::assertSent(ErrorReportMail::class, 1);
        Mail::assertSent(ErrorReportMail::class, function (ErrorReportMail $mail): bool {
            $mail->assertSeeInText('RuntimeException');

            return $mail->hasTo('admin@example.test')
                && $mail->exceptionMessage === 'PDF kon niet worden gemaakt';
        });
    }

    public function test_errors_are_not_mailed_outside_production(): void
    {
        Mail::fake();

        $this->app->make(ExceptionHandler::class)->report(new RuntimeException('Lokaal'));

        Mail::assertNothingSent();
    }
}
