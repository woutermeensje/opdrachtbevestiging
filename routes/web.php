<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationNotificationController;
use App\Http\Controllers\EmailVerificationPromptController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\KvkLookupController;
use App\Http\Controllers\KvkSearchController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicConfirmationController;
use App\Http\Controllers\SignhostWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/signhost/webhook', SignhostWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('signhost.webhook');
Route::get('/api/signhost/webhook', [SignhostWebhookController::class, 'status'])
    ->name('signhost.webhook.status');

Route::get('/robots.txt', function (): Response {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /dashboard',
        'Disallow: /inloggen',
        'Disallow: /registreren',
        'Disallow: /opdrachtbevestiging/',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ]);

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

Route::get('/sitemap.xml', function (): Response {
    $pages = collect([
        ['loc' => url('/'), 'priority' => '1.0'],
        ['loc' => route('pages.how-it-works'), 'priority' => '0.8'],
        ['loc' => route('pages.what-is-confirmation'), 'priority' => '0.8'],
        ['loc' => route('pages.create-confirmation'), 'priority' => '0.8'],
        ['loc' => route('pages.pricing'), 'priority' => '0.7'],
        ['loc' => route('pages.contact'), 'priority' => '0.6'],
    ]);

    return response()->view('seo.sitemap', [
        'pages' => $pages,
        'lastModified' => now()->toAtomString(),
    ])->header('Content-Type', 'application/xml; charset=UTF-8');
});

Route::get('/hoe-het-werkt', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/wat-is-een-opdrachtbevestiging', [PageController::class, 'whatIsConfirmation'])->name('pages.what-is-confirmation');
Route::get('/opdrachtbevestiging-opstellen', [PageController::class, 'createConfirmation'])->name('pages.create-confirmation');
Route::get('/prijzen', [PageController::class, 'pricing'])->name('pages.pricing');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/opdrachtbevestiging/{token}', [PublicConfirmationController::class, 'show'])->name('confirmations.public.show');
Route::post('/opdrachtbevestiging/{token}/ondertekenen', [PublicConfirmationController::class, 'sign'])->name('confirmations.public.sign');
Route::post('/kvk/lookup', KvkLookupController::class)->name('kvk.lookup');
Route::post('/kvk/search', KvkSearchController::class)->name('kvk.search');

Route::redirect('/register', '/registreren');
Route::redirect('/login', '/inloggen');

Route::middleware('guest')->group(function (): void {
    Route::get('/registreren', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registreren', [AuthController::class, 'register'])->name('register.store');
    Route::get('/inloggen', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->name('login.store');
    Route::get('/wachtwoord-vergeten', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/wachtwoord-vergeten', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/wachtwoord-instellen/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/wachtwoord-instellen', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/email/bevestigen', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/email/bevestigen/{id}/{hash}', VerifyEmailController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/bevestiging-opnieuw-versturen', EmailVerificationNotificationController::class)
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/aanmaken', [ConfirmationController::class, 'create'])->name('dashboard.create');
    Route::post('/dashboard/aanmaken', [ConfirmationController::class, 'store'])->name('dashboard.create.store');
    Route::get('/dashboard/opdrachtbevestigingen', [ConfirmationController::class, 'index'])->name('dashboard.confirmations');
    Route::get('/dashboard/opdrachtbevestigingen/{confirmation}', [ConfirmationController::class, 'show'])->name('dashboard.confirmations.show');
    Route::post('/dashboard/opdrachtbevestigingen/{confirmation}/verzenden', [ConfirmationController::class, 'send'])->name('dashboard.confirmations.send');
    Route::get('/dashboard/opdrachtbevestigingen/{confirmation}/ondertekend-document', [ConfirmationController::class, 'downloadSignedDocument'])->name('dashboard.confirmations.download-signed-document');
    Route::get('/dashboard/opdrachtbevestigingen/{confirmation}/receipt', [ConfirmationController::class, 'downloadReceipt'])->name('dashboard.confirmations.download-receipt');
    Route::get('/dashboard/contacten', [ContactController::class, 'index'])->name('dashboard.contacts');
    Route::post('/dashboard/contacten', [ContactController::class, 'store'])->name('dashboard.contacts.store');
    Route::get('/dashboard/mijn-profiel', [DashboardController::class, 'profile'])->name('dashboard.profile');
});
