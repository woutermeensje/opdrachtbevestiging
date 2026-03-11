<?php

namespace App\Http\Controllers;

use App\Services\SignhostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignhostWebhookController extends Controller
{
    public function __construct(
        private readonly SignhostService $signhostService,
    ) {
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'postback_url' => config('services.signhost.postback_url'),
            'has_webhook_bearer_token' => config('services.signhost.webhook_bearer_token') !== null
                && config('services.signhost.webhook_bearer_token') !== '',
        ]);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $configuredToken = (string) config('services.signhost.webhook_bearer_token');
        $providedToken = $request->bearerToken() ?? '';

        abort_if(
            $configuredToken === '' || ! hash_equals($configuredToken, $providedToken),
            401,
            'Unauthorized'
        );

        Log::info('Signhost webhook received', [
            'headers' => [
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->userAgent(),
            ],
            'payload' => $request->json()->all() ?: $request->all(),
        ]);

        $this->signhostService->handlePostback($request->json()->all() ?: $request->all());

        return response()->json([
            'received' => true,
        ]);
    }
}
