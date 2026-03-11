<?php

namespace Tests\Feature;

use App\Notifications\AdminNewUserRegisteredNotification;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_and_admin_notifications(): void
    {
        Notification::fake();
        config()->set('mail.admin_address', 'support@sustainablejobs.nl');

        $response = $this->post(route('register.store'), [
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'kvk_number' => '12345678',
            'company_name' => 'Opdrachtbevestiging',
            'street_name' => 'Dorpsstraat',
            'house_number' => '12',
            'house_number_addition' => 'A',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'email' => 'wouter@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'wouter@example.com',
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'company_name' => 'Opdrachtbevestiging',
        ]);

        $user = User::where('email', 'wouter@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
        Notification::assertSentOnDemand(AdminNewUserRegisteredNotification::class, function ($notification, array $channels, object $notifiable): bool {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'support@sustainablejobs.nl';
        });
    }

    public function test_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'kvk_number' => '12345678',
            'company_name' => 'Opdrachtbevestiging',
            'street_name' => 'Dorpsstraat',
            'house_number' => '12',
            'house_number_addition' => 'A',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'email' => 'wouter@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_user_is_redirected_to_email_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_from_link(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'wouter@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'wouter@example.com',
            'password' => 'oudepassword123',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nieuwpassword123',
            'password_confirmation' => 'nieuwpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(password_verify('nieuwpassword123', $user->fresh()->password));
        $this->assertFalse(Password::broker()->getRepository()->exists($user, $token));
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_kvk_lookup_returns_company_and_address_data(): void
    {
        config()->set('kvk.api_key', 'test-key');
        config()->set('kvk.base_url', 'https://api.kvk.nl/test/api/v1');

        Http::fake([
            'https://api.kvk.nl/test/api/v1/basisprofielen/12345678' => Http::response([
                'naam' => 'Opdrachtbevestiging B.V.',
                'hoofdvestiging' => [
                    'adressen' => [
                        [
                            'type' => 'bezoekadres',
                            'straatnaam' => 'Dorpsstraat',
                            'huisnummer' => 12,
                            'huisnummerToevoeging' => 'A',
                            'postcode' => '1234AB',
                            'plaats' => 'Amsterdam',
                            'land' => 'Nederland',
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->postJson(route('kvk.lookup'), [
            'kvk_number' => '12345678',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.company_name', 'Opdrachtbevestiging B.V.')
            ->assertJsonPath('data.street_name', 'Dorpsstraat')
            ->assertJsonPath('data.house_number', '12')
            ->assertJsonPath('data.house_number_addition', 'A')
            ->assertJsonPath('data.postal_code', '1234AB')
            ->assertJsonPath('data.city', 'Amsterdam')
            ->assertJsonPath('data.country', 'Nederland');
    }
}
