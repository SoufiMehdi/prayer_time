<?php

namespace App\Services;

use DateTime;
use DateTimeZone;

class NextPrayerSerice
{
    public function getNextPrayer(array $timings) : array
    {
        $now = new DateTime('now',new DateTimeZone('Europe/Paris'));
        $currentTime = $now->format('H:i');
        foreach ($timings as $prayerName => $prayerTime ) {
            if ($prayerName === 'Sunrise') {
                continue;
            }
            dump($currentTime, $prayerTime);
            if ($prayerTime > $currentTime) {
                $prayerDateTime = \DateTime::createFromFormat('H:i', $prayerTime);
                $interval = $now->diff($prayerDateTime);

                return [
                    'name' => $prayerName,
                    'time' => $prayerTime,
                    'remaining' => [
                        'hours' => $interval->h,
                        'minutes' => $interval->i,
                        'formatted' => sprintf('%dh %dm', $interval->h, $interval->i)
                    ]
                ];
            }
        }
        return [
            'name' => 'Fajr',
            'time' => $timings['Fajr'] ?? null,
            'tomorrow' => true
        ];
    }
}
