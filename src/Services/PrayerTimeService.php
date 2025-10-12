<?php

namespace App\Services;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class PrayerTimeService
{

    public function __construct(
        private readonly HttpClientInterface $prayerApi,
    ) {
    }
    public function getPrayerTime(
        string $city,
        string $country,
        int $methode
    ): array {
        $response = $this->prayerApi->request('GET', '/timingsByCity', [
            'query' => [
                'city' => $city,
                'country' => $country,
                'methode' => $methode,
            ]
        ]);
        $data = $response->toArray();
        return $data['data']['timings'];
    }
}
