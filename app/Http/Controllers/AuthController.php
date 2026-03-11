<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AdminNewUserRegisteredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'kvk_number' => ['required', 'digits:8'],
            'company_name' => ['required', 'string', 'max:255'],
            'street_name' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:20'],
            'house_number_addition' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'company_name' => $validated['company_name'],
            'kvk_number' => $validated['kvk_number'],
            'street_name' => $validated['street_name'],
            'house_number' => $validated['house_number'],
            'house_number_addition' => $validated['house_number_addition'] ?? null,
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        if (filled(config('mail.admin_address'))) {
            Notification::route('mail', config('mail.admin_address'))
                ->notify(new AdminNewUserRegisteredNotification($user));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'De inloggegevens zijn onjuist.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
