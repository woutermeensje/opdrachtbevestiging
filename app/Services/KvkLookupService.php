<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class KvkLookupService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function lookupByKvkNumber(string $kvkNumber): array
    {
        $apiKey = (string) config('kvk.api_key');
        $baseUrl = rtrim((string) config('kvk.base_url'), '/');

        if ($apiKey === '') {
            throw new RuntimeException('De KVK API is nog niet geconfigureerd.');
        }

        try {
            $response = $this->http
                ->baseUrl($baseUrl)
                ->timeout((int) config('kvk.timeout', 10))
                ->acceptJson()
                ->withHeaders([
                    'apikey' => $apiKey,
                ])
                ->get("/basisprofielen/{$kvkNumber}")
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException('KVK-gegevens konden niet worden opgehaald.', previous: $exception);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();
        $mainBranch = $this->firstPresent($payload, [
            'hoofdvestiging',
            '_embedded.hoofdvestiging',
        ]);

        $addresses = $this->firstPresent((array) $mainBranch, [
            'adressen',
            '_embedded.adressen',
        ]);

        $address = $this->pickPrimaryAddress(is_array($addresses) ? $addresses : []);

        return [
            'kvk_number' => $kvkNumber,
            'company_name' => $this->stringValue($this->firstPresent($payload, [
                'naam',
                'statutaireNaam',
                'eersteHandelsnaam',
                'onderneming.naam',
                'onderneming.statutaireNaam',
            ])),
            'street_name' => $this->stringValue($this->firstPresent($address, [
                'straatnaam',
                'straat',
            ])),
            'house_number' => $this->stringValue($this->firstPresent($address, [
                'huisnummer',
                'nummer',
            ])),
            'house_number_addition' => $this->stringValue($this->firstPresent($address, [
                'huisnummerToevoeging',
                'toevoeging',
            ])),
            'postal_code' => $this->stringValue($this->firstPresent($address, [
                'postcode',
            ])),
            'city' => $this->stringValue($this->firstPresent($address, [
                'plaats',
                'woonplaats',
            ])),
            'country' => $this->stringValue($this->firstPresent($address, [
                'land',
                'landcode',
            ])) ?: 'Nederland',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function lookupByCompanyName(string $companyName): array
    {
        $apiKey = (string) config('kvk.api_key');
        $searchBaseUrl = rtrim((string) config('kvk.search_base_url', $this->deriveSearchBaseUrl()), '/');

        if ($apiKey === '') {
            throw new RuntimeException('De KVK API is nog niet geconfigureerd.');
        }

        $result = $this->pickBestSearchResult(
            $this->searchPayload($companyName, $searchBaseUrl, $apiKey),
            $companyName
        );
        $kvkNumber = $this->stringValue($this->firstPresent($result, [
            'kvkNummer',
            'kvknummer',
            'kvk_number',
        ]));

        if ($kvkNumber === null) {
            throw new RuntimeException('Er is geen KVK-resultaat gevonden voor deze bedrijfsnaam.');
        }

        return $this->lookupByKvkNumber($kvkNumber);
    }

    /**
     * @return array<int, array{company_name: string, kvk_number: ?string, city: ?string}>
     */
    public function searchCompanies(string $companyName): array
    {
        $apiKey = (string) config('kvk.api_key');
        $searchBaseUrl = rtrim((string) config('kvk.search_base_url', $this->deriveSearchBaseUrl()), '/');
        $payload = $this->searchPayload($companyName, $searchBaseUrl, $apiKey);
        $results = $this->firstPresent($payload, [
            'resultaten',
            'items',
            '_embedded.resultaten',
            '_embedded.items',
        ]);

        if (! is_array($results)) {
            return [];
        }

        return collect($results)
            ->filter(fn ($result) => is_array($result))
            ->take(8)
            ->map(function (array $result): array {
                return [
                    'company_name' => $this->stringValue($this->firstPresent($result, ['naam', 'handelsnaam'])) ?? 'Onbekend',
                    'kvk_number' => $this->stringValue($this->firstPresent($result, ['kvkNummer', 'kvknummer', 'kvk_number'])),
                    'city' => $this->stringValue($this->firstPresent($result, [
                        'adres.binnenlandsAdres.plaats',
                        'adres.plaats',
                        'plaats',
                    ])),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, string|null>
     */
    public function lookup(string $identifier): array
    {
        $normalized = trim($identifier);

        if (preg_match('/^\d{8}$/', $normalized) === 1) {
            return $this->lookupByKvkNumber($normalized);
        }

        return $this->lookupByCompanyName($normalized);
    }

    /**
     * @param  array<int, mixed>  $addresses
     * @return array<string, mixed>
     */
    private function pickPrimaryAddress(array $addresses): array
    {
        foreach ($addresses as $address) {
            if (! is_array($address)) {
                continue;
            }

            $type = mb_strtolower((string) ($address['type'] ?? ''));

            if (in_array($type, ['bezoekadres', 'vestigingsadres', 'hoofdvestiging'], true)) {
                return $address;
            }
        }

        return is_array($addresses[0] ?? null) ? $addresses[0] : [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $paths
     */
    private function firstPresent(array $data, array $paths): mixed
    {
        foreach ($paths as $path) {
            $value = data_get($data, $path);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function deriveSearchBaseUrl(): string
    {
        $baseUrl = rtrim((string) config('kvk.base_url'), '/');

        return str_replace('/api/v1', '/api/v2', $baseUrl);
    }

    /**
     * @return array<string, mixed>
     */
    private function searchPayload(string $companyName, string $searchBaseUrl, string $apiKey): array
    {
        if ($apiKey === '') {
            throw new RuntimeException('De KVK API is nog niet geconfigureerd.');
        }

        try {
            $response = $this->http
                ->baseUrl($searchBaseUrl)
                ->timeout((int) config('kvk.timeout', 10))
                ->acceptJson()
                ->withHeaders([
                    'apikey' => $apiKey,
                ])
                ->get('/zoeken', [
                    'naam' => $companyName,
                ])
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException('KVK-zoekresultaten konden niet worden opgehaald.', previous: $exception);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function pickBestSearchResult(array $payload, string $companyName): array
    {
        $results = $this->firstPresent($payload, [
            'resultaten',
            'items',
            '_embedded.resultaten',
            '_embedded.items',
        ]);

        if (! is_array($results)) {
            return [];
        }

        $normalizedCompanyName = mb_strtolower(trim($companyName));

        foreach ($results as $result) {
            if (! is_array($result)) {
                continue;
            }

            $name = $this->stringValue($this->firstPresent($result, [
                'naam',
                'handelsnaam',
            ]));

            if ($name !== null && mb_strtolower($name) === $normalizedCompanyName) {
                return $result;
            }
        }

        return is_array($results[0] ?? null) ? $results[0] : [];
    }
}
