<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
class HijriCalendarService
{
    public function __construct(
       private readonly HttpClientInterface $prayerApi,
       private readonly LoggerInterface $logger
    ) {
    }
    public function getHolidaysForMonth(string $month, string $year): array
    {
        try {
            $response = $this->prayerApi->request('GET', '/gToHCalendar/'.$month.'/'.$year, [
               'query' => [
                   'adjustment' => 18,
               ]
            ]);
            $data = $response->toArray();
            if ($data['code'] !== 200) {
                throw new \RuntimeException('API returned an error: ' . ($data['status'] ?? 'Unknown error'));
            }
            return $this->extractHolidaysForMonth($data);
        }catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
            return [];
        }
    }
    private function extractHolidaysForMonth(array $data): array
    {
        $holidays = [];
        foreach ($data['data'] as $day) {
            if (!empty($day['hijri']['holidays'])) {
                $holidays[] = [
                    'gregorian_date' => $day['gregorian']['date'],
                    'hijri_date' => $day['hijri']['date'],
                    'holidays' => $day['hijri']['holidays']
                ];
            }
        }
        return $holidays;
    }
}
