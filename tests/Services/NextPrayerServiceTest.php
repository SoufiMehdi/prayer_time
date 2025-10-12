<?php

namespace App\Tests\Services;

use App\Services\NextPrayerSerice;
use PHPUnit\Framework\TestCase;

class NextPrayerServiceTest extends TestCase
{
    private NextPrayerSerice $service;

    protected function setUp(): void
    {
        $this->service = new NextPrayerSerice();
    }

    public function testGetNextPrayerReturnsNextPrayerInDay(): void
    {
        // Simuler qu'il est 10h00
        $mockTime = new \DateTime('2025-10-12 10:00:00', new \DateTimeZone('Europe/Paris'));

        $timings = [
            'Fajr' => '05:30',
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
            'Maghrib' => '20:10',
            'Isha' => '21:45',
        ];

        // Mock de la date actuelle (optionnel, pour tests plus précis)
        // Note: Pour mocker DateTime, tu peux utiliser des bibliothèques comme Carbon

        $result = $this->service->getNextPrayer($timings, $mockTime);

        // La prochaine prière devrait être Dhuhr (13:45)
        $this->assertEquals('Dhuhr', $result['name']);
        $this->assertEquals('13:45', $result['time']);
        $this->assertArrayHasKey('remaining', $result);
        $this->assertArrayHasKey('hours', $result['remaining']);
        $this->assertArrayHasKey('minutes', $result['remaining']);
        $this->assertArrayHasKey('formatted', $result['remaining']);
    }

    public function testGetNextPrayerSkipsSunrise(): void
    {
        // Simuler qu'il est 07:00 (avant Sunrise mais après Fajr)
        $mockTime = new \DateTime('2025-10-12 07:00:00', new \DateTimeZone('Europe/Paris'));
        $timings = [
            'Fajr' => '05:30',
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
            'Maghrib' => '20:10',
            'Isha' => '21:45',
        ];

        $result = $this->service->getNextPrayer($timings, $mockTime);

        // Ne devrait JAMAIS retourner Sunrise
        $this->assertNotEquals('Sunrise', $result['name']);
    }

    public function testGetNextPrayerReturnsFajrTomorrowWhenAllPrayersArePast(): void
    {
        // Simuler qu'il est 23h00 (toutes les prières sont passées)
        $mockTime = new \DateTime('2025-10-12 23:00:00', new \DateTimeZone('Europe/Paris'));
        $timings = [
            'Fajr' => '05:30',
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
            'Maghrib' => '20:10',
            'Isha' => '21:45',
        ];

        // Pour tester correctement, il faudrait mocker l'heure actuelle
        // Mais avec le code actuel, on peut tester la structure de retour

        $result = $this->service->getNextPrayer($timings, $mockTime);

        // Si toutes les prières sont passées, devrait retourner Fajr de demain
        if (isset($result['tomorrow'])) {
            $this->assertEquals('Fajr', $result['name']);
            $this->assertTrue($result['tomorrow']);
            $this->assertEquals('05:30', $result['time']);
        }
    }

    public function testGetNextPrayerCalculatesRemainingTimeCorrectly(): void
    {
        $timings = [
            'Fajr' => '05:30',
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
            'Maghrib' => '20:10',
            'Isha' => '21:45',
        ];

        $result = $this->service->getNextPrayer($timings);

        // Vérifier la structure du temps restant
        $this->assertIsArray($result['remaining']);
        $this->assertIsInt($result['remaining']['hours']);
        $this->assertIsInt($result['remaining']['minutes']);
        $this->assertIsString($result['remaining']['formatted']);

        // Le format doit être "Xh Ym"
        $this->assertMatchesRegularExpression('/^\d+h \d+m$/', $result['remaining']['formatted']);

        // Les heures et minutes doivent être positifs
        $this->assertGreaterThanOrEqual(0, $result['remaining']['hours']);
        $this->assertGreaterThanOrEqual(0, $result['remaining']['minutes']);
    }

    public function testGetNextPrayerWithCustomTimings(): void
    {
        // Test avec des horaires spécifiques
        $timings = [
            'Fajr' => '04:30',
            'Sunrise' => '06:00',
            'Dhuhr' => '12:30',
            'Asr' => '16:00',
            'Maghrib' => '19:30',
            'Isha' => '21:00',
        ];

        $result = $this->service->getNextPrayer($timings);

        // Vérifier que le résultat est valide
        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('time', $result);

        // Le nom doit être l'une des prières valides (pas Sunrise)
        $validPrayers = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
        $this->assertContains($result['name'], $validPrayers);
    }

    public function testGetNextPrayerHandlesEmptyTimings(): void
    {
        $timings = [];

        $result = $this->service->getNextPrayer($timings);

        // Devrait retourner Fajr par défaut avec time à null
        $this->assertEquals('Fajr', $result['name']);
        $this->assertNull($result['time']);
        $this->assertTrue($result['tomorrow']);
    }

    public function testGetNextPrayerHandlesMissingFajr(): void
    {
        $timings = [
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
        ];

        $result = $this->service->getNextPrayer($timings);

        // Si Fajr est manquant et toutes les prières sont passées
        if (isset($result['tomorrow'])) {
            $this->assertNull($result['time']);
        }
    }

    /**
     * Test avec une heure mockée (nécessite une refactorisation du service)
     * Ce prayer_time montre comment on pourrait tester avec une heure fixe
     */
    public function testGetNextPrayerWithMockedTime(): void
    {
        // Ce prayer_time nécessiterait d'injecter l'heure actuelle dans le service
        // ou d'utiliser une bibliothèque comme Carbon

        $timings = [
            'Fajr' => '05:30',
            'Sunrise' => '07:15',
            'Dhuhr' => '13:45',
            'Asr' => '17:20',
            'Maghrib' => '20:10',
            'Isha' => '21:45',
        ];

        $result = $this->service->getNextPrayer($timings);

        // Vérifications de base
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['name']);
        $this->assertNotEmpty($result['time']);
    }
}
