<?php

namespace Tests\Feature;

use Tests\TestCase;

class SignhostWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('session.driver', 'array');
    }

    public function test_signhost_webhook_status_endpoint_reports_configuration_state(): void
    {
        config()->set('services.signhost.postback_url', 'https://opdrachtbevestiging.nl/api/signhost/webhook');
        config()->set('services.signhost.webhook_bearer_token', 'expected-token');

        $response = $this->getJson(route('signhost.webhook.status'));

        $response
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'postback_url' => 'https://opdrachtbevestiging.nl/api/signhost/webhook',
                'has_webhook_bearer_token' => true,
            ]);
    }

    public function test_signhost_webhook_requires_valid_bearer_token(): void
    {
        config()->set('services.signhost.webhook_bearer_token', 'expected-token');

        $response = $this->postJson(route('signhost.webhook'), [
            'transaction' => [
                'id' => 'tx-123',
            ],
        ]);

        $response->assertUnauthorized();
    }

    public function test_signhost_webhook_accepts_valid_bearer_token(): void
    {
        config()->set('services.signhost.webhook_bearer_token', 'expected-token');

        $response = $this
            ->withHeader('Authorization', 'Bearer expected-token')
            ->postJson(route('signhost.webhook'), [
                'transaction' => [
                    'id' => 'tx-123',
                    'status' => 'completed',
                ],
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'received' => true,
            ]);
    }
}
