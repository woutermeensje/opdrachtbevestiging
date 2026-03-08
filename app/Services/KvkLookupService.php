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
    public function lookup(string $kvkNumber): array
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
}
