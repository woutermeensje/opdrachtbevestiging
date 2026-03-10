<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KvkLookupController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicConfirmationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
