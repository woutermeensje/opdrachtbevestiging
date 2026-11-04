<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
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
            ->withMax(['activityLogs as last_login_at' => fn ($query) => $query->where('action', 'auth.login')], 'created_at')
            ->latest()
            ->get();

        return view('admin.dashboard', [
            'metrics' => [
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'confirmations' => Confirmation::query()->published()->count(),
                'quotes' => Quote::count(),
                'logins_today' => ActivityLog::query()
                    ->where('action', 'auth.login')
                    ->where('created_at', '>=', today())
                    ->distinct()
                    ->count('user_id'),
            ],
            'users' => $users,
            'recentActivities' => ActivityLog::query()
                ->with('subject')
                ->latest('created_at')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }
}
