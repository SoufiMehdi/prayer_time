<?php

namespace App\Tests\Services;

use App\Services\PrayerTimeService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PrayerTimeServiceTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    private PrayerTimeService $service;

    protected function setUp(): void
    {
        // Créer des mocks pour les dépendances
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // Instancier le service avec les mocks
        $this->service = new PrayerTimeService(
            $this->httpClient,
            $this->cache,
            $this->logger
        );
    }

    public function testGetPrayerTimeReturnsFormattedData(): void
    {
        // Données de l'API mockées
        $apiResponse = [
            'code' => 200,
            'status' => 'OK',
            'data' => [
                'timings' => [
                    'Fajr' => '05:30 (CEST)',
                    'Sunrise' => '07:15 (CEST)',
                    'Dhuhr' => '13:45 (CEST)',
                    'Asr' => '17:20 (CEST)',
                    'Maghrib' => '20:10 (CEST)',
                    'Isha' => '21:45 (CEST)',
                ],
                'date' => [
                    'readable' => '12 Oct 2025'
                ],
                'meta' => [
                    'method' => [
                        'id' => 2,
                        'name' => 'ISNA'
                    ]
                ]
            ]
        ];

        // Mock de la réponse HTTP
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        // Mock du HttpClient
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/timingsByCity', [
                'query' => [
                    'city' => 'Paris',
                    'country' => 'France',
                    'methode' => 2,
                ]
            ])
            ->willReturn($response);

        // Mock du cache - simule qu'il n'y a pas de cache
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        // Appel du service
        $result = $this->service->getPrayerTime('Paris', 'France', 2);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('timings', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('meta', $result);

        // Vérifier que les timings sont formatés (sans timezone)
        $this->assertEquals('05:30', $result['timings']['Fajr']);
        $this->assertEquals('07:15', $result['timings']['Sunrise']);
        $this->assertEquals('13:45', $result['timings']['Dhuhr']);
        $this->assertEquals('17:20', $result['timings']['Asr']);
        $this->assertEquals('20:10', $result['timings']['Maghrib']);
        $this->assertEquals('21:45', $result['timings']['Isha']);
    }

    public function testGetPrayerTimeThrowsExceptionWhenApiReturnsError(): void
    {
        $apiResponse = [
            'code' => 400,
            'status' => 'Bad Request',
            'data' => null
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->cache
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        // Le logger doit être appelé pour l'erreur
        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch prayer times');

        $this->service->getPrayerTime('InvalidCity', 'InvalidCountry', 2);
    }

    public function testGetPrayerTimeUsesCache(): void
    {
        $cachedData = [
            'timings' => [
                'Fajr' => '05:30',
                'Dhuhr' => '13:45',
            ],
            'date' => '12 Oct 2025',
            'meta' => []
        ];

        // Le cache retourne directement les données
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->willReturn($cachedData);

        // L'API ne doit PAS être appelée
        $this->httpClient
            ->expects($this->never())
            ->method('request');

        $result = $this->service->getPrayerTime('Paris', 'France', 2);

        $this->assertEquals($cachedData, $result);
    }

    public function testFormatTimingsRemovesTimezone(): void
    {
        $rawTimings = [
            'Fajr' => '05:30 (CEST)',
            'Sunrise' => '07:15 (CEST)',
            'Dhuhr' => '13:45 (CEST)',
            'Asr' => '17:20 (CEST)',
            'Maghrib' => '20:10 (CEST)',
            'Isha' => '21:45 (CEST)',
            'Imsak' => '05:20 (CEST)', // Ne devrait pas être inclus
        ];

        $formatted = $this->service->formatTimings($rawTimings);

        // Vérifier que seules les 6 prières sont retournées
        $this->assertCount(6, $formatted);

        // Vérifier que le timezone est supprimé
        $this->assertEquals('05:30', $formatted['Fajr']);
        $this->assertEquals('07:15', $formatted['Sunrise']);
        $this->assertEquals('13:45', $formatted['Dhuhr']);
        $this->assertEquals('17:20', $formatted['Asr']);
        $this->assertEquals('20:10', $formatted['Maghrib']);
        $this->assertEquals('21:45', $formatted['Isha']);

        // Vérifier que Imsak n'est pas inclus
        $this->assertArrayNotHasKey('Imsak', $formatted);
    }

    public function testGenerateCacheKeyIsUnique(): void
    {
        $key1 = $this->service->generateCacheKey('Paris', 'France', 2);
        $key2 = $this->service->generateCacheKey('Lyon', 'France', 2);
        $key3 = $this->service->generateCacheKey('Paris', 'France', 3);

        // Les clés doivent être différentes
        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);

        // La clé doit contenir la date
        $this->assertStringContainsString(date('Y-m-d'), $key1);

        // Format attendu
        $expectedFormat = sprintf(
            'prayer_time_Paris_France_2_%s',
            date('Y-m-d')
        );
        $this->assertEquals($expectedFormat, $key1);
    }

    public function testGetPrayerTimeLogsInfoOnApiCall(): void
    {
        $apiResponse = [
            'code' => 200,
            'data' => [
                'timings' => ['Fajr' => '05:30'],
                'date' => ['readable' => '12 Oct 2025'],
                'meta' => []
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient->method('request')->willReturn($response);

        $this->cache
            ->method('get')
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        // Vérifier que le logger est appelé avec les bons paramètres
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Fetching prayer times from API',
                [
                    'city' => 'Paris',
                    'country' => 'France',
                    'method' => 2
                ]
            );

        $this->service->getPrayerTime('Paris', 'France', 2);
    }
}
