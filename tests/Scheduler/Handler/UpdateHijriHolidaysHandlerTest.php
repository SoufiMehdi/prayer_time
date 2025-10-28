<?php

namespace App\Tests\Scheduler\Handler;

use App\Entity\IslamicHoliday;
use App\Message\UpdateHijriHolidaysMessage;
use App\Scheduler\Handler\UpdateHijriHolidaysHandler;
use App\Services\HijriCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateHijriHolidaysHandlerTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private HijriCalendarService|MockObject $holidaysService;
    private EntityRepository|MockObject $repository;
    private UpdateHijriHolidaysHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->holidaysService = $this->createMock(HijriCalendarService::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->with(IslamicHoliday::class)
            ->willReturn($this->repository);

        $this->handler = new UpdateHijriHolidaysHandler(
            $this->entityManager,
            $this->holidaysService
        );
    }

    public function testHandlerCreatesNewHolidaysSuccessfully(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage(new \DateTime('2025-03-01'));

        $holidays = [
            [
                'gregorian_date' => '30-03-2025',
                'hijri_date' => '01-09-1446',
                'holidays' => ['1st day of Ramadan', 'Shawwal']
            ]
        ];

        $this->holidaysService
            ->expects($this->once())
            ->method('getHolidaysForMonth')
            ->with('03', '2025')
            ->willReturn($holidays);

        // Le repository ne trouve aucun holiday existant
        $this->repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        // On s'attend à 2 persist (2 holidays)
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function ($holiday) {
                return $holiday instanceof IslamicHoliday;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        ob_start();
        ($this->handler)($message);
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('2 holidays récupérés', $output);
        $this->assertStringContainsString('March 2025', $output);
    }

    public function testHandlerSkipsExistingHolidays(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage(new \DateTime('2025-04-01'));

        $holidays = [
            [
                'gregorian_date' => '29-04-2025',
                'hijri_date' => '01-10-1446',
                'holidays' => ['Eid-ul-Fitr']
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        // Le holiday existe déjà
        $existingHoliday = new IslamicHoliday();
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'name' => 'Eid-ul-Fitr',
                'dateGregorian' => new \DateTime('29-04-2025')
            ])
            ->willReturn($existingHoliday);

        // Aucun persist ne doit être appelé
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();
    }

    public function testHandlerTranslatesHolidayNamesCorrectly(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage();

        $holidays = [
            [
                'gregorian_date' => '30-03-2025',
                'hijri_date' => '01-09-1446',
                'holidays' => ['1st day of Ramadan']
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        $this->repository
            ->method('findOneBy')
            ->willReturn(null);

        $capturedHoliday = null;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($holiday) use (&$capturedHoliday) {
                $capturedHoliday = $holiday;
                return true;
            }));

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();

        // Assert
        $this->assertInstanceOf(IslamicHoliday::class, $capturedHoliday);
        $this->assertEquals('1st day of Ramadan', $capturedHoliday->getName());
        $this->assertEquals('Premier jour du Ramadan', $capturedHoliday->getNameFr());
    }

    public function testHandlerSetsPassedStatusCorrectly(): void
    {
        // Arrange
        $futureDate = (new \DateTime())->modify('+30 days');
        $message = new UpdateHijriHolidaysMessage($futureDate);

        $futureDateFormatted = $futureDate->format('d-m-Y');

        $holidays = [
            [
                'gregorian_date' => $futureDateFormatted,
                'hijri_date' => '01-09-1446',
                'holidays' => ['Future Holiday']
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        $this->repository
            ->method('findOneBy')
            ->willReturn(null);

        $capturedHoliday = null;
        $this->entityManager
            ->method('persist')
            ->with($this->callback(function ($holiday) use (&$capturedHoliday) {
                $capturedHoliday = $holiday;
                return true;
            }));

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();

        // Assert
        $this->assertFalse($capturedHoliday->isPassed(), 'Future holiday should not be marked as passed');
    }

    public function testHandlerUsesCurrentDateWhenMessageHasNoDate(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage(); // Pas de date spécifiée

        $now = new \DateTime();
        $expectedMonth = $now->format('m');
        $expectedYear = $now->format('Y');

        $this->holidaysService
            ->expects($this->once())
            ->method('getHolidaysForMonth')
            ->with($expectedMonth, $expectedYear)
            ->willReturn([]);

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();
    }

    public function testHandlerHandlesMultipleHolidaysOnSameDay(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage(new \DateTime('2025-06-01'));

        $holidays = [
            [
                'gregorian_date' => '15-06-2025',
                'hijri_date' => '20-11-1446',
                'holidays' => ['Eid-ul-Adha', 'Ashura', 'Lailat-ul-Qadr']
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        $this->repository
            ->method('findOneBy')
            ->willReturn(null);

        // On s'attend à 3 persist (3 holidays le même jour)
        $this->entityManager
            ->expects($this->exactly(3))
            ->method('persist');

        // Act
        ob_start();
        ($this->handler)($message);
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('3 holidays récupérés', $output);
    }

    public function testHandlerHandlesEmptyHolidaysList(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage(new \DateTime('2025-01-01'));

        $this->holidaysService
            ->expects($this->once())
            ->method('getHolidaysForMonth')
            ->willReturn([]);

        // Aucun persist ne doit être appelé
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        ob_start();
        ($this->handler)($message);
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('0 holidays récupérés', $output);
    }

    public function testHandlerTranslatesFallbackForUnknownHolidays(): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage();

        $holidays = [
            [
                'gregorian_date' => '01-01-2025',
                'hijri_date' => '01-07-1446',
                'holidays' => ['Unknown holiday name']
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        $this->repository
            ->method('findOneBy')
            ->willReturn(null);

        $capturedHoliday = null;
        $this->entityManager
            ->method('persist')
            ->with($this->callback(function ($holiday) use (&$capturedHoliday) {
                $capturedHoliday = $holiday;
                return true;
            }));

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();

        // Assert
        $this->assertEquals('Unknown holiday name', $capturedHoliday->getName());
        $this->assertEquals('Unknown holiday name', $capturedHoliday->getNameFr()); // ucfirst appliqué
    }

    #[DataProvider('holidayTranslationProvider')]
    public function testHandlerTranslatesAllKnownHolidays(string $input, string $expected): void
    {
        // Arrange
        $message = new UpdateHijriHolidaysMessage();

        $holidays = [
            [
                'gregorian_date' => '01-01-2025',
                'hijri_date' => '01-07-1446',
                'holidays' => [$input]
            ]
        ];

        $this->holidaysService
            ->method('getHolidaysForMonth')
            ->willReturn($holidays);

        $this->repository
            ->method('findOneBy')
            ->willReturn(null);

        $capturedHoliday = null;
        $this->entityManager
            ->method('persist')
            ->with($this->callback(function ($holiday) use (&$capturedHoliday) {
                $capturedHoliday = $holiday;
                return true;
            }));

        // Act
        ob_start();
        ($this->handler)($message);
        ob_get_clean();

        // Assert
        $this->assertEquals($expected, $capturedHoliday->getNameFr());
    }

    public static function holidayTranslationProvider(): array
    {
        return [
            ['1st day of Ramadan', 'Premier jour du Ramadan'],
            ['1ST DAY OF RAMADAN', 'Premier jour du Ramadan'], // Test case insensitive
            ['  1st day of Ramadan  ', 'Premier jour du Ramadan'], // Test trim
            ['Eid-ul-Fitr', 'Aïd al-Fitr'],
            ['EID-UL-FITR', 'Aïd al-Fitr'],
            ['Eid-ul-Adha', 'Aïd al-Adha'],
            ['Mawlid-ul-Nabi', 'Mawlid an-Nabi (Naissance du Prophète)'],
            ['Ashura', 'Achoura'],
            ['Isra and Miraj', 'Isra et Miraj'],
            ['Lailat-ul-Qadr', 'La nuit du destin'],
        ];
    }
}
