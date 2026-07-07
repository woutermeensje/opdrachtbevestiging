<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $confirmations = $user->confirmations()->latest()->take(5)->get();

        return view('dashboard.index', [
            'metrics' => [
                'total' => $user->confirmations()->count(),
                'drafts' => $user->confirmations()->where('status', 'concept')->count(),
                'signed' => $user->confirmations()->where('status', 'getekend')->count(),
                'value' => (float) $user->confirmations()->sum('total_value'),
            ],
            'contactCount' => $user->contacts()->count(),
            'recentConfirmations' => $confirmations,
        ]);
    }

    public function profile(): View
    {
        return view('dashboard.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'kvk_number' => ['required', 'digits:8'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'house_number_addition' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('dashboard.profile')
            ->with('status', 'Je bedrijfsgegevens zijn opgeslagen.');
    }
}
