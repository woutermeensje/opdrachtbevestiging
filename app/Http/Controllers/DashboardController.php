<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
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
            'recentConfirmations' => $confirmations,
        ]);
    }

    public function profile(): View
    {
        return view('dashboard.profile');
    }
}
