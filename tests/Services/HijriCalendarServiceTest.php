<?php

namespace App\Tests\Services;

use App\Services\HijriCalendarService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HijriCalendarServiceTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClient;
    private LoggerInterface|MockObject $logger;
    private HijriCalendarService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new HijriCalendarService(
            $this->httpClient,
            $this->logger
        );
    }

    public function testGetHolidaysForMonthReturnsHolidaysSuccessfully(): void
    {
        // Arrange
        $month = '03';
        $year = '2025';

        $apiResponse = [
            'code' => 200,
            'status' => 'OK',
            'data' => [
                [
                    'gregorian' => ['date' => '30-03-2025'],
                    'hijri' => [
                        'date' => '01-09-1446',
                        'holidays' => ['Ramadan']
                    ]
                ],
                [
                    'gregorian' => ['date' => '31-03-2025'],
                    'hijri' => [
                        'date' => '02-09-1446',
                        'holidays' => []
                    ]
                ],
                [
                    'gregorian' => ['date' => '29-04-2025'],
                    'hijri' => [
                        'date' => '01-10-1446',
                        'holidays' => ['Eid al-Fitr', 'Shawwal']
                    ]
                ]
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/gToHCalendar/03/2025',
                ['query' => ['adjustment' => 18]]
            )
            ->willReturn($response);

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals([
            [
                'gregorian_date' => '30-03-2025',
                'hijri_date' => '01-09-1446',
                'holidays' => ['Ramadan']
            ],
            [
                'gregorian_date' => '29-04-2025',
                'hijri_date' => '01-10-1446',
                'holidays' => ['Eid al-Fitr', 'Shawwal']
            ]
        ], $result);
    }

    public function testGetHolidaysForMonthReturnsEmptyArrayWhenNoHolidays(): void
    {
        // Arrange
        $month = '01';
        $year = '2025';

        $apiResponse = [
            'code' => 200,
            'status' => 'OK',
            'data' => [
                [
                    'gregorian' => ['date' => '01-01-2025'],
                    'hijri' => [
                        'date' => '01-07-1446',
                        'holidays' => []
                    ]
                ],
                [
                    'gregorian' => ['date' => '02-01-2025'],
                    'hijri' => [
                        'date' => '02-07-1446',
                        'holidays' => []
                    ]
                ]
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert
        $this->assertEmpty($result);
    }

    public function testGetHolidaysForMonthThrowsExceptionWhenApiReturnsError(): void
    {
        // Arrange
        $month = '12';
        $year = '2025';

        $apiResponse = [
            'code' => 400,
            'status' => 'Bad Request',
            'data' => []
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('API returned an error'));

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert
        $this->assertEmpty($result);
    }

    public function testGetHolidaysForMonthHandlesHttpException(): void
    {
        // Arrange
        $month = '06';
        $year = '2025';

        $this->httpClient
            ->method('request')
            ->willThrowException(new \Exception('Network error'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Network error');

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert
        $this->assertEmpty($result);
    }

    public function testGetHolidaysForMonthHandlesInvalidJsonResponse(): void
    {
        // Arrange
        $month = '08';
        $year = '2025';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willThrowException(new \Exception('Invalid JSON'));

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Invalid JSON');

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert
        $this->assertEmpty($result);
    }

    public function testGetHolidaysForMonthUsesCorrectAdjustmentParameter(): void
    {
        // Arrange
        $month = '11';
        $year = '2025';

        $apiResponse = [
            'code' => 200,
            'status' => 'OK',
            'data' => []
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return isset($options['query']['adjustment'])
                        && $options['query']['adjustment'] === 18;
                })
            )
            ->willReturn($response);

        // Act
        $this->service->getHolidaysForMonth($month, $year);
    }

    public function testGetHolidaysForMonthHandlesMissingHijriDataGracefully(): void
    {
        // Arrange
        $month = '05';
        $year = '2025';

        $apiResponse = [
            'code' => 200,
            'status' => 'OK',
            'data' => [
                [
                    'gregorian' => ['date' => '01-05-2025'],
                    'hijri' => [
                        'date' => '01-11-1446',
                        'holidays' => ['Labor Day']
                    ]
                ],
                [
                    'gregorian' => ['date' => '02-05-2025'],
                    // hijri key missing
                ]
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($apiResponse);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->logger
            ->expects($this->once())
            ->method('error');

        // Act
        $result = $this->service->getHolidaysForMonth($month, $year);

        // Assert - Should handle gracefully and return empty array
        $this->assertEmpty($result);
    }
}
