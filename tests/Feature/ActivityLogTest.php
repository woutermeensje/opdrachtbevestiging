<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_and_failed_logins_are_logged(): void
    {
        $user = User::factory()->create(['email' => 'jan@example.com']);

        $this->post(route('login.store'), ['email' => 'jan@example.com', 'password' => 'fout']);
        $this->post(route('login.store'), ['email' => 'jan@example.com', 'password' => 'password']);
        $this->post(route('logout'));

        $this->assertSame(
            ['auth.failed', 'auth.login', 'auth.logout'],
            ActivityLog::query()->orderBy('id')->pluck('action')->all(),
        );
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.login',
            'user_id' => $user->id,
            'user_email' => 'jan@example.com',
        ]);
    }

    public function test_successful_actions_are_logged_with_subject(): void
    {
        $user = User::factory()->create(['company_name' => 'Testbedrijf']);

        $this->actingAs($user)->post(route('dashboard.contacts.store'), [
            'company_name' => 'Voorbeeld B.V.',
            'street_name' => 'Keizersgracht',
            'house_number' => '1',
            'postal_code' => '1015CJ',
            'city' => 'Amsterdam',
            'contact_first_name' => 'Piet',
            'contact_last_name' => 'de Boer',
            'contact_email' => 'piet@example.test',
            'contact_phone' => '0612345678',
        ]);

        $contact = Contact::firstOrFail();

        $this->actingAs($user)->patch(route('dashboard.contacts.update', $contact), [
            'company_name' => 'Voorbeeld Holding B.V.',
            'street_name' => 'Keizersgracht',
            'house_number' => '1',
            'postal_code' => '1015CJ',
            'city' => 'Amsterdam',
            'contact_first_name' => 'Piet',
            'contact_last_name' => 'de Boer',
            'contact_email' => 'piet@example.test',
            'contact_phone' => '0612345678',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'dashboard.contacts.store',
            'description' => 'Opdrachtgever toegevoegd',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'dashboard.contacts.update',
            'subject_type' => $contact->getMorphClass(),
            'subject_id' => $contact->id,
        ]);
    }

    public function test_failed_validation_is_not_logged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.contacts.store'), [])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('activity_logs', ['action' => 'dashboard.contacts.store']);
    }

    public function test_admin_can_view_and_filter_activity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $jan = User::factory()->create(['name' => 'Jan Jansen']);
        $piet = User::factory()->create(['name' => 'Piet Pietersen']);

        ActivityLog::create(['user_id' => $jan->id, 'user_name' => $jan->name, 'user_email' => $jan->email, 'action' => 'auth.login', 'description' => 'Ingelogd']);
        ActivityLog::create(['user_id' => $piet->id, 'user_name' => $piet->name, 'user_email' => $piet->email, 'action' => 'dashboard.quotes.send', 'description' => 'Offerte verzonden']);

        $this->actingAs($admin)
            ->get(route('admin.activity'))
            ->assertOk()
            ->assertSeeText('Jan Jansen')
            ->assertSeeText('Offerte verzonden');

        $this->actingAs($admin)
            ->get(route('admin.activity', ['user' => $jan->id]))
            ->assertOk()
            ->assertSeeText('1 resultaten')
            ->assertViewHas('activities', fn ($activities) => $activities->pluck('action')->all() === ['auth.login']);

        $this->actingAs($admin)
            ->get(route('admin.activity', ['action' => 'auth.*']))
            ->assertOk()
            ->assertSeeText('1 resultaten');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Recente activiteit')
            ->assertSeeText('Laatst ingelogd');
    }

    public function test_activity_page_requires_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.activity'))
            ->assertForbidden();
    }
}
