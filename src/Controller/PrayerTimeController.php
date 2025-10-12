<?php

namespace App\Controller;

use App\Services\PrayerTimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PrayerTimeController extends AbstractController
{
    #[Route('/api/prayer/time', name: 'app_prayer_time')]
    public function getPrayerTimes(Request $request, PrayerTimeService $prayerTimeService): JsonResponse
    {
       $city = $request->get("city", "Paris");
       $country = $request->get("country", "France");
       $methode = 2;
       $timings = $prayerTimeService->getPrayerTime($city, $country, $methode);
        return $this->json([
            'city' => $city,
            'country' => $country,
            'timing' => $timings
        ]);
    }
}
