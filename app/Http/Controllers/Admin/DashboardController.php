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
        $users = User::query()
            ->select([
                'id',
                'name',
                'company_name',
                'email',
                'email_verified_at',
                'is_admin',
                'subscription_status',
                'subscription_plan',
                'pending_subscription_plan',
                'trial_ends_at',
                'subscription_renews_at',
                'created_at',
            ])
            ->latest()
            ->get();

        return view('admin.dashboard', [
            'metrics' => [
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'confirmations' => Confirmation::count(),
                'quotes' => Quote::count(),
            ],
            'users' => $users,
        ]);
    }
}
