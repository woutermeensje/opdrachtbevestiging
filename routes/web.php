<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KvkLookupController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hoe-het-werkt', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/prijzen', [PageController::class, 'pricing'])->name('pages.pricing');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');

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
    Route::get('/dashboard/aanmaken', [DashboardController::class, 'create'])->name('dashboard.create');
    Route::get('/dashboard/opdrachtbevestigingen', [DashboardController::class, 'confirmations'])->name('dashboard.confirmations');
    Route::get('/dashboard/mijn-profiel', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
