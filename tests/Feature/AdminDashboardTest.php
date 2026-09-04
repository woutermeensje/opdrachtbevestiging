<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_requires_admin_user(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_registered_users_with_account_statuses(): void
    {
        $admin = User::factory()->create([
            'name' => 'Wouter Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'subscription_status' => 'active',
            'subscription_plan' => 'monthly',
            'subscription_renews_at' => now()->addMonth(),
        ]);

        User::factory()->create([
            'name' => 'Actieve Gebruiker',
            'email' => 'active@example.com',
            'company_name' => 'Actief B.V.',
            'subscription_status' => 'active',
            'subscription_plan' => 'yearly',
            'subscription_renews_at' => now()->addYear(),
        ]);

        User::factory()->create([
            'name' => 'Trial Gebruiker',
            'email' => 'trial@example.com',
            'company_name' => null,
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(7),
        ]);

        User::factory()->unverified()->create([
            'name' => 'Onbevestigde Gebruiker',
            'email' => 'unverified@example.com',
        ]);

        User::factory()->create([
            'name' => 'Wachtende Gebruiker',
            'email' => 'pending@example.com',
            'subscription_status' => 'pending',
            'pending_subscription_plan' => 'monthly',
        ]);

        User::factory()->create([
            'name' => 'Verlopen Gebruiker',
            'email' => 'expired@example.com',
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Aangemelde gebruikers')
            ->assertSeeText('Wouter Admin')
            ->assertSeeText('Admin')
            ->assertSeeText('Actieve Gebruiker')
            ->assertSeeText('active@example.com')
            ->assertSeeText('Actief B.V.')
            ->assertSeeText('Jaarlijks')
            ->assertSeeText('Trial Gebruiker')
            ->assertSeeText('Nog niet ingevuld')
            ->assertSeeText('Proefperiode')
            ->assertSeeText('Onbevestigde Gebruiker')
            ->assertSeeText('E-mail niet bevestigd')
            ->assertSeeText('Wachtende Gebruiker')
            ->assertSeeText('Betaling in behandeling')
            ->assertSeeText('Verlopen Gebruiker')
            ->assertSeeText('Proefperiode verlopen');
    }
}
