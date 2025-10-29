<?php

namespace App\Controller;

use App\Services\NextPrayerSerice;
use App\Services\PrayerTimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PrayerTimeController extends AbstractController
{
    #[Route('/api/prayer/time', name: 'app_prayer_time')]
    public function getPrayerTimes(
        Request $request,
        PrayerTimeService $prayerTimeService,
        NextPrayerSerice $nextPrayerSerice
    ): JsonResponse {
       $city = $request->get("city", "Paris");
       $country = $request->get("country", "France");
       $methode = 12;
       $timings = $prayerTimeService->getPrayerTime($city, $country, $methode);
       $nextPrayer = $nextPrayerSerice->getNextPrayer($timings['timings']);
        return $this->json([
            'city' => $city,
            'country' => $country,
            'timing' => $timings,
            'date' => $timings['date'],
            'hijri' => $timings['hijri'],
            'nextPrayer' => $nextPrayer
        ]);
    }
}
