<?php
// src/Scheduler/HijriHolidaysScheduleProvider.php
namespace App\Scheduler;

use App\Message\UpdateHijriHolidaysMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('hijri_holidays')]
class HijriHolidaysScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::cron('*/10 * * * *', new UpdateHijriHolidaysMessage())
            );
    }
}
