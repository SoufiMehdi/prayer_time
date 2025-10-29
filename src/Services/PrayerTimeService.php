<?php

namespace App\Services;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class PrayerTimeService
{
    private const CACHE_TTL = 86400;
    public function __construct(
        private readonly HttpClientInterface $prayerApi,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }
    public function getPrayerTime(
        string $city,
        string $country,
        int $methode
    ): array {
        $cacheKey = $this->generateCacheKey($city, $country, $methode);
        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city, $country, $methode) {
                $item->expiresAfter(self::CACHE_TTL);
                $this->logger->info('Fetching prayer times from API', [
                    'city' => $city,
                    'country' => $country,
                    'method' => $methode
                ]);
                $now = new \DateTime();
                $response = $this->prayerApi->request('GET', '/timingsByCity/'.$now->format('d-m-Y'), [
                    'query' => [
                        'city' => $city,
                        'country' => $country,
                        'methode' => $methode,
                    ]
                ]);
                $data = $response->toArray();
                if ($data['code'] !== 200) {
                    throw new \RuntimeException('API returned an error: ' . ($data['status'] ?? 'Unknown error'));
                }
                return [
                    'timings' => $this->formatTimings($data['data']['timings']),
                    'date' => $data['data']['date']['readable'] ?? date('d M Y'),
                    'hijri' => $data['data']['date']['hijri'] ?? [],
                    'meta' => $data['data']['meta'] ?? []
                ];
            });
        }catch (\Exception $e){
            $this->logger->error('Error fetching prayer times', [
                'error' => $e->getMessage(),
                'city' => $city,
                'country' => $country
            ]);
            throw new \RuntimeException('Unable to fetch prayer times: ' . $e->getMessage());
        }
    }
    public function formatTimings(array $timings): array
    {
        $prayerNames = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        $formatted = [];

        foreach ($prayerNames as $prayer) {
            if (isset($timings[$prayer])) {
                $formatted[$prayer] = explode(' ', $timings[$prayer])[0];
            }
        }

        return $formatted;
    }
    public function generateCacheKey(string $city, string $contry,int $methode): string {
        $date = date('Y-m-d');
        return sprintf('prayer_time_%s_%s_%d_%s',
            $city,
            $contry,
            $methode,
            $date
        );
    }
}
