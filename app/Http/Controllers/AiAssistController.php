<?php

namespace App\Http\Controllers;

use Anthropic\Client as AnthropicClient;
use App\Models\Confirmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AiAssistController extends Controller
{
    public function improveConfirmationText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:8000'],
            'context' => ['nullable', 'string', 'max:100'],
        ]);

        $plainText = Confirmation::richTextToPlainText(
            Confirmation::sanitizeDescription($validated['text'])
        );

        if (trim($plainText) === '') {
            return response()->json(['message' => 'Vul eerst een tekst in om te verbeteren.'], 422);
        }

        $apiKey = config('services.anthropic.key');

        if (! $apiKey) {
            return response()->json(['message' => 'AI-assist is niet geconfigureerd.'], 500);
        }

        $dailyLimit = (int) config('services.anthropic.daily_limit', 30);
        $limiterKey = 'ai-assist:'.$request->user()->id;

        if (RateLimiter::tooManyAttempts($limiterKey, $dailyLimit)) {
            $hoursLeft = (int) ceil(RateLimiter::availableIn($limiterKey) / 3600);

            return response()->json([
                'message' => "Je hebt het dagelijkse maximum van {$dailyLimit} AI-assist-aanroepen bereikt. Probeer het over ongeveer {$hoursLeft} uur opnieuw.",
            ], 429);
        }

        RateLimiter::hit($limiterKey, 86400);

        $client = new AnthropicClient(apiKey: $apiKey);

        $response = $client->messages->create(
            maxTokens: 2048,
            model: config('services.anthropic.model', 'claude-sonnet-5'),
            thinking: ['type' => 'disabled'],
            system: 'Je bent een assistent die Nederlandse teksten voor zakelijke opdrachtbevestigingen verbetert. '
                .'Je krijgt een ruwe tekst van de gebruiker (context: '.($validated['context'] ?? 'opdrachtbevestiging').'). '
                .'Herschrijf deze tot een heldere, juridisch zorgvuldig geformuleerde en professioneel gestructureerde tekst in het Nederlands. '
                .'Behoud de inhoudelijke intentie en alle feitelijke details van de gebruiker; verzin geen nieuwe afspraken of bedragen. '
                .'Gebruik waar zinvol alinea\'s en opsommingen om de tekst overzichtelijk te maken. '
                .'Antwoord uitsluitend met de verbeterde tekst als HTML, en gebruik daarbij alleen de tags <p>, <br>, <strong>, <em>, <ul>, <ol> en <li>. '
                .'Geen inleiding, geen toelichting, geen markdown, geen andere HTML-tags.',
            messages: [
                ['role' => 'user', 'content' => $plainText],
            ],
        );

        $improvedText = collect($response->content)
            ->filter(fn ($block) => $block->type === 'text')
            ->map(fn ($block) => $block->text)
            ->implode('');

        $improvedHtml = Confirmation::sanitizeDescription($improvedText);

        if ($improvedHtml === null) {
            return response()->json(['message' => 'Er kon geen verbeterde tekst worden gegenereerd.'], 502);
        }

        return response()->json(['html' => $improvedHtml]);
    }
}
