<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KvkLookupController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicConfirmationController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

Route::redirect('/register', '/registreren');
Route::redirect('/login', '/inloggen');

Route::middleware('guest')->group(function (): void {
    Route::get('/registreren', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registreren', [AuthController::class, 'register'])->name('register.store');
    Route::post('/kvk/lookup', KvkLookupController::class)->name('kvk.lookup');
    Route::get('/inloggen', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/aanmaken', [ConfirmationController::class, 'create'])->name('dashboard.create');
    Route::post('/dashboard/aanmaken', [ConfirmationController::class, 'store'])->name('dashboard.create.store');
    Route::get('/dashboard/opdrachtbevestigingen', [ConfirmationController::class, 'index'])->name('dashboard.confirmations');
    Route::get('/dashboard/opdrachtbevestigingen/{confirmation}', [ConfirmationController::class, 'show'])->name('dashboard.confirmations.show');
    Route::post('/dashboard/opdrachtbevestigingen/{confirmation}/verzenden', [ConfirmationController::class, 'send'])->name('dashboard.confirmations.send');
    Route::get('/dashboard/mijn-profiel', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
