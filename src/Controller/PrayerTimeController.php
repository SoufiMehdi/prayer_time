<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PrayerTimeController extends AbstractController
{
    #[Route('/api/prayer/time', name: 'app_prayer_time')]
    public function getPrayerTimes(Request $request): JsonResponse
    {
       $city = $request->get("city", "Paris");
       $country = $request->get("country", "France");
       $client = HttpClient::create();
       $response = $client->request('GET', 'https://api.aladhan.com/v1/timingsByCity',[
           "query" => [
               "city" => $city,
               "country" => $country,
               "method" => 2
           ]
           ]);
       $data = $response->toArray();
       $timings = $data["data"]["timings"];
        return $this->json([
            'city' => $city,
            'country' => $country,
            'timing' => $timings
        ]);
    }
}
