<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignhostWebhookController extends Controller
{
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

        return response()->json([
            'received' => true,
        ]);
    }
}
