<?php

namespace App\Scheduler\Handler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Services\HijriCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\UpdateHijriHolidaysMessage;
use App\Entity\IslamicHoliday;
#[AsMessageHandler]
class UpdateHijriHolidaysHandler
{
    public function __construct(
      private readonly EntityManagerInterface $entityManager,
      private readonly HijriCalendarService $holidaysService,
    ) {
    }
    public function __invoke(UpdateHijriHolidaysMessage $message): void
    {
        $date = $message->getForMonth() ?? new \DateTime();
        $daysHaveHolidays = $this->holidaysService->getHolidaysForMonth(
            $date->format('m'),
            $date->format('Y')
        );
        $counHolidays = 0;
        foreach ($daysHaveHolidays as $day) {
            $gregorianDate = $day['gregorian_date'];
            $hijriDate = $day['hijri_date'];
            foreach ($day['holidays'] as $holidayName) {
                $counHolidays += 1;
                $existing = $this->entityManager->getRepository(IslamicHoliday::class)->findOneBy([
                    'name' => $holidayName,
                    'dateGregorian' => new \DateTime($gregorianDate),
                ]);
                if ($existing) {
                    continue;
                }
                $holiday = new IslamicHoliday();
                $holiday->setName($holidayName);
                $holiday->setNameFr($this->translateHoliday($holidayName));
                $holiday->setDateGregorian(new \DateTime($gregorianDate));
                $holiday->setDateHijri($hijriDate);
                $holiday->setPassed(new \DateTimeImmutable() > \DateTimeImmutable::createFromFormat('d-m-Y',$gregorianDate));
                $this->entityManager->persist($holiday);
            }

        }
        $this->entityManager->flush();
        echo sprintf(
            "✅ %d holidays récupérés pour %s\n",
            $counHolidays,
            $date->format('F Y')
        );
    }
    private function translateHoliday(string $holiday): string
    {
        return match (trim(strtolower($holiday))) {
            '1st day of ramadan' => 'Premier jour du Ramadan',
            'eid-ul-fitr' => 'Aïd al-Fitr',
            'eid-ul-adha' => 'Aïd al-Adha',
            'mawlid-ul-nabi' => 'Mawlid an-Nabi (Naissance du Prophète)',
            'ashura' => 'Achoura',
            'isra and miraj' => 'Isra et Miraj',
            'lailat-ul-qadr' => 'La nuit du destin',
            default => ucfirst($holiday),
        };
    }
}
