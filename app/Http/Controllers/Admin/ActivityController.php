<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'user' => ['nullable', 'integer'],
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
        ]);

        $activities = ActivityLog::query()
            ->with('subject')
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['action'] ?? null, fn ($query, $action) => str_ends_with($action, '.*')
                ? $query->where('action', 'like', str($action)->beforeLast('*')->value().'%')
                : $query->where('action', $action))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->where('created_at', '>=', $from))
            ->when($filters['until'] ?? null, fn ($query, $until) => $query->where('created_at', '<', now()->parse($until)->addDay()))
            ->latest('created_at')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.activity.index', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'actionOptions' => $this->actionOptions(),
        ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function actionOptions(): array
    {
        return [
            'Groepen' => [
                'auth.*' => 'Alle inlog-activiteit',
                'dashboard.confirmations.*' => 'Opdrachtbevestigingen (verzenden, intrekken, PDF)',
                'dashboard.quotes.*' => 'Alle offerte-acties',
                'dashboard.profile.*' => 'Alle profielwijzigingen',
                'dashboard.ai-assist.*' => 'Alle AI-acties',
            ],
            'Inloggen' => [
                'auth.login' => 'Ingelogd',
                'auth.logout' => 'Uitgelogd',
                'auth.failed' => 'Mislukte inlogpoging',
                'auth.registered' => 'Account geregistreerd',
                'auth.verified' => 'E-mailadres bevestigd',
                'auth.password_reset' => 'Wachtwoord opnieuw ingesteld',
            ],
            'Acties' => config('activity.actions'),
        ];
    }
}
