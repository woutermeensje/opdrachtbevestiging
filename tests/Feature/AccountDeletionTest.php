<?php

namespace Tests\Feature;

use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_profile_page_shows_delete_account_button(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Account verwijderen');
    }

    public function test_user_can_delete_account_with_correct_password(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);
        $confirmation = Confirmation::factory()->create(['user_id' => $user->id]);

        Storage::disk('local')->put('profiles/'.$user->id.'/logo/logo.png', 'logo');
        Storage::disk('local')->put('confirmations/'.$confirmation->id.'/pdf/bevestiging.pdf', 'pdf');

        $this
            ->actingAs($user)
            ->delete(route('dashboard.profile.account.destroy'), ['delete_password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($contact);
        $this->assertModelMissing($confirmation);
        Storage::disk('local')->assertMissing('profiles/'.$user->id.'/logo/logo.png');
        Storage::disk('local')->assertMissing('confirmations/'.$confirmation->id.'/pdf/bevestiging.pdf');
    }

    public function test_account_is_not_deleted_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->from(route('dashboard.profile'))
            ->delete(route('dashboard.profile.account.destroy'), ['delete_password' => 'verkeerd'])
            ->assertRedirect(route('dashboard.profile'))
            ->assertSessionHasErrors('delete_password');

        $this->assertModelExists($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_active_mollie_subscription_is_cancelled_before_deletion(): void
    {
        config()->set('billing.mollie_key', 'test_key');

        Http::fake([
            'api.mollie.com/v2/customers/cst_123/subscriptions/sub_123' => Http::response([], 204),
        ]);

        $user = User::factory()->create([
            'subscription_status' => 'active',
            'mollie_customer_id' => 'cst_123',
            'mollie_subscription_id' => 'sub_123',
        ]);

        $this
            ->actingAs($user)
            ->delete(route('dashboard.profile.account.destroy'), ['delete_password' => 'password'])
            ->assertRedirect(route('login'));

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/customers/cst_123/subscriptions/sub_123'));
        $this->assertModelMissing($user);
    }

    public function test_account_is_kept_when_subscription_cannot_be_cancelled(): void
    {
        config()->set('billing.mollie_key', 'test_key');

        Http::fake([
            'api.mollie.com/*' => Http::response(['detail' => 'Fout'], 500),
        ]);

        $user = User::factory()->create([
            'subscription_status' => 'active',
            'mollie_customer_id' => 'cst_123',
            'mollie_subscription_id' => 'sub_123',
        ]);

        $this
            ->actingAs($user)
            ->delete(route('dashboard.profile.account.destroy'), ['delete_password' => 'password'])
            ->assertRedirect(route('dashboard.profile'))
            ->assertSessionHasErrors('delete_password');

        $this->assertModelExists($user);
    }
}
