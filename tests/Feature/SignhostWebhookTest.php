<?php

namespace Tests\Feature;

use Tests\TestCase;

class SignhostWebhookTest extends TestCase
{
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
