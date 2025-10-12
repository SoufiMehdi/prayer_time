<?php

namespace App\Tests\Controller;

use App\Kernel;
use App\Services\NextPrayerSerice;
use App\Services\PrayerTimeService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PrayerTimeControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
    public function testGetPrayerTimesWithDefaultParameters(): void
    {
        $client = static::createClient();

        // Mock du PrayerTimeService
        $prayerTimeService = $this->createMock(PrayerTimeService::class);
        $prayerTimeService->method('getPrayerTime')
            ->with('Paris', 'France', 12)
            ->willReturn([
                'timings' => [
                    'Fajr' => '05:30',
                    'Sunrise' => '07:15',
                    'Dhuhr' => '13:45',
                    'Asr' => '17:20',
                    'Maghrib' => '20:10',
                    'Isha' => '21:45',
                ],
                'date' => '12 Oct 2025',
                'meta' => [
                    'method' => ['id' => 12, 'name' => 'UOIF']
                ]
            ]);

        // Mock du NextPrayerService
        $nextPrayerService = $this->createMock(NextPrayerSerice::class);
        $nextPrayerService->method('getNextPrayer')
            ->willReturn([
                'name' => 'Dhuhr',
                'time' => '13:45',
                'remaining' => [
                    'hours' => 3,
                    'minutes' => 45,
                    'formatted' => '3h 45m'
                ]
            ]);

        // Remplacer les services dans le container
        static::getContainer()->set(PrayerTimeService::class, $prayerTimeService);
        static::getContainer()->set(NextPrayerSerice::class, $nextPrayerService);

        // Faire la requête
        $client->request('GET', '/api/prayer/time');

        // Assertions
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Vérifier la structure de la réponse
        $this->assertArrayHasKey('city', $responseData);
        $this->assertArrayHasKey('country', $responseData);
        $this->assertArrayHasKey('timing', $responseData);
        $this->assertArrayHasKey('nextPrayer', $responseData);

        // Vérifier les valeurs par défaut
        $this->assertEquals('Paris', $responseData['city']);
        $this->assertEquals('France', $responseData['country']);

        // Vérifier les timings
        $this->assertArrayHasKey('timings', $responseData['timing']);
        $this->assertEquals('05:30', $responseData['timing']['timings']['Fajr']);
        $this->assertEquals('13:45', $responseData['timing']['timings']['Dhuhr']);

        // Vérifier nextPrayer
        $this->assertEquals('Dhuhr', $responseData['nextPrayer']['name']);
        $this->assertEquals('13:45', $responseData['nextPrayer']['time']);
    }

}
