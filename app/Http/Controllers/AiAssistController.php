<?php

namespace App\Http\Controllers;

use Anthropic\Client as AnthropicClient;
use App\Models\Confirmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use JsonException;
use Throwable;

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

        if ($unavailable = $this->unavailableResponse($request)) {
            return $unavailable;
        }

        try {
            $improvedText = $this->message(
                system: 'Je bent een assistent die Nederlandse teksten voor zakelijke opdrachtbevestigingen verbetert. '
                    .'Je krijgt een ruwe tekst van de gebruiker (context: '.($validated['context'] ?? 'opdrachtbevestiging').'). '
                    .'Herschrijf deze tot een heldere, juridisch zorgvuldig geformuleerde en professioneel gestructureerde tekst in het Nederlands. '
                    .'Behoud de inhoudelijke intentie en alle feitelijke details van de gebruiker; verzin geen nieuwe afspraken of bedragen. '
                    .'Gebruik waar zinvol alinea\'s en opsommingen om de tekst overzichtelijk te maken. '
                    .'Als de tekst een volledige opdrachtbeschrijving is, gebruik dan de vaste opbouw voor opdrachtbeschrijvingen. '
                    .$this->draftStructureRules().' '
                    .$this->htmlOutputRules(),
                content: $plainText,
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'AI-assist is tijdelijk niet beschikbaar. Probeer het later opnieuw.'], 502);
        }

        $improvedHtml = Confirmation::sanitizeDescription($improvedText);

        if ($improvedHtml === null) {
            return response()->json(['message' => 'Er kon geen verbeterde tekst worden gegenereerd.'], 502);
        }

        return response()->json(['html' => $improvedHtml]);
    }

    public function generateConfirmationDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brief' => ['required', 'string', 'max:4000'],
            'form_context' => ['nullable', 'string', 'max:12000'],
        ]);

        if ($unavailable = $this->unavailableResponse($request)) {
            return $unavailable;
        }

        try {
            $draft = $this->message(
                system: 'Je bent een assistent die Nederlandse opdrachtbevestigingen opstelt voor ondernemers. '
                    .'Maak van de korte input een duidelijke opdrachtbeschrijving in professioneel Nederlands. '
                    .'Gebruik uitsluitend feiten die de gebruiker of formuliercontext geeft. Verzin geen bedragen, datums, namen, looptijden of afspraken. '
                    .'Als informatie ontbreekt, benoem die niet als fictieve afspraak en gebruik geen placeholders zoals [datum]. '
                    .$this->draftStructureRules().' '
                    .$this->htmlOutputRules(),
                content: trim($validated['brief'])."\n\nFormuliercontext:\n".trim($validated['form_context'] ?? ''),
                maxTokens: 1600,
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'AI-assist is tijdelijk niet beschikbaar. Probeer het later opnieuw.'], 502);
        }

        $draftHtml = Confirmation::sanitizeDescription($draft);

        if ($draftHtml === null) {
            return response()->json(['message' => 'Er kon geen concepttekst worden gegenereerd.'], 502);
        }

        return response()->json(['html' => $draftHtml]);
    }

    public function checkConfirmationCompleteness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['nullable', 'string', 'max:8000'],
            'form_context' => ['nullable', 'string', 'max:12000'],
        ]);

        $plainText = Confirmation::richTextToPlainText(
            Confirmation::sanitizeDescription($validated['text'] ?? '')
        );
        $formContext = trim($validated['form_context'] ?? '');

        if (trim($plainText.$formContext) === '') {
            return response()->json(['message' => 'Vul eerst gegevens in om te controleren.'], 422);
        }

        if ($unavailable = $this->unavailableResponse($request)) {
            return $unavailable;
        }

        try {
            $checkText = $this->message(
                system: 'Je controleert Nederlandse zakelijke opdrachtbevestigingen op duidelijkheid en compleetheid. '
                    .'Geef geen juridisch advies en herschrijf de tekst niet. Controleer alleen of de afspraken begrijpelijk en volledig genoeg zijn. '
                    .'Beoordeel minimaal: opdrachtbeschrijving/scope, planning, prijs of tarief, btw/betaalafspraken, reiskosten, materialen, opdrachtgever, opdrachtnemer, oplevering, meerwerk, verantwoordelijkheden, opzegging/annulering en akkoordproces. '
                    .'Gebruik alleen de aangeleverde informatie. Als iets ontbreekt, zeg dat concreet. '
                    .'Antwoord uitsluitend als geldige JSON zonder markdown, met deze vorm: '
                    .'{"score":75,"summary":"Korte samenvatting","items":[{"label":"Planning","status":"ok","message":"..."},{"label":"Betaling","status":"missing","message":"..."}]}. '
                    .'Gebruik alleen statuswaarden ok, warning of missing.',
                content: "Opdrachtbeschrijving:\n{$plainText}\n\nFormuliercontext:\n{$formContext}",
                maxTokens: 1800,
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'AI-assist is tijdelijk niet beschikbaar. Probeer het later opnieuw.'], 502);
        }

        $decoded = $this->decodeJson($checkText);

        if (! is_array($decoded)) {
            return response()->json(['message' => 'De AI-controle gaf geen bruikbaar resultaat terug.'], 502);
        }

        return response()->json($this->normaliseCheckResult($decoded));
    }

    private function unavailableResponse(Request $request): ?JsonResponse
    {
        if (! config('services.anthropic.key')) {
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

        return null;
    }

    private function message(string $system, string $content, int $maxTokens = 2048): string
    {
        $client = new AnthropicClient(apiKey: config('services.anthropic.key'));

        $response = $client->messages->create(
            maxTokens: $maxTokens,
            model: config('services.anthropic.model', 'claude-sonnet-5'),
            thinking: ['type' => 'disabled'],
            system: $system,
            messages: [
                ['role' => 'user', 'content' => $content],
            ],
        );

        return collect($response->content)
            ->filter(fn ($block) => $block->type === 'text')
            ->map(fn ($block) => $block->text)
            ->implode('');
    }

    private function draftStructureRules(): string
    {
        return implode(' ', [
            'Gebruik deze vaste HTML-opbouw voor opdrachtbeschrijvingen:',
            '<p><strong>Opdracht</strong></p> met daarna een korte alinea over de opdracht.',
            '<p><strong>Afspraken</strong></p> met daarna een <ul> met concrete afspraken.',
            '<p><strong>Planning en vergoeding</strong></p> alleen als hierover concrete informatie is gegeven.',
            '<p><strong>Voorwaarden</strong></p> alleen als hierover concrete informatie is gegeven.',
            'Start nooit met een losse titel zoals Opdrachtbevestiging.',
            'Gebruik koppen alleen als <p><strong>Koptekst</strong></p>.',
            'Schrijf geen afsluitende restalinea over ontbrekende informatie.',
            'Als de gebruiker expliciet zegt dat iets nog niet bekend is, benoem dat alleen bij dat concrete onderdeel.',
        ]);
    }

    private function htmlOutputRules(): string
    {
        return implode(' ', [
            'Antwoord uitsluitend als veilige HTML.',
            'Gebruik alleen de tags <p>, <br>, <strong>, <em>, <ul>, <ol> en <li>.',
            'Geen inleiding, geen toelichting, geen markdown en geen andere HTML-tags.',
            'Gebruik geen inline styles, classes, tabellen, headings of links.',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $text): ?array
    {
        $json = trim($text);
        $json = preg_replace('/^```(?:json)?|```$/m', '', $json) ?? $json;
        $json = trim($json);

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            if (! preg_match('/\{.*\}/s', $json, $matches)) {
                return null;
            }

            try {
                $decoded = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return null;
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array{score: int, summary: string, items: array<int, array{label: string, status: string, message: string}>}
     */
    private function normaliseCheckResult(array $result): array
    {
        $allowedStatuses = ['ok', 'warning', 'missing'];

        return [
            'score' => max(0, min(100, (int) ($result['score'] ?? 0))),
            'summary' => trim(strip_tags((string) ($result['summary'] ?? 'Controle afgerond.'))),
            'items' => collect($result['items'] ?? [])
                ->filter(fn ($item): bool => is_array($item))
                ->map(function (array $item) use ($allowedStatuses): array {
                    $status = (string) ($item['status'] ?? 'warning');

                    return [
                        'label' => trim(strip_tags((string) ($item['label'] ?? 'Aandachtspunt'))),
                        'status' => in_array($status, $allowedStatuses, true) ? $status : 'warning',
                        'message' => trim(strip_tags((string) ($item['message'] ?? 'Controleer dit onderdeel.'))),
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
