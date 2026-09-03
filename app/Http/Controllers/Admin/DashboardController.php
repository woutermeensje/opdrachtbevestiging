<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Confirmation;
use App\Models\Quote;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'metrics' => [
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'confirmations' => Confirmation::count(),
                'quotes' => Quote::count(),
            ],
        ]);
    }
}
