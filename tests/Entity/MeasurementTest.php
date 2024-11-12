<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Measurement;

class MeasurementTest extends TestCase
{
    /**
     * Data provider for testGetFahrenheit
     */
    public function dataGetFahrenheit(): array
    {
        return [
            ['0', 32],
            ['-100', -148],
            ['100', 212],
            ['0.5', 32.9],
            ['-50', -58],
            ['25', 77],
            ['-10', 14],
            ['37.5', 99.5],
            ['-20', -4],
            ['10.1', 50.18],
        ];
    }

    /**
     * Testowanie metody getFahrenheit() z użyciem dataProvider
     *
     * @dataProvider dataGetFahrenheit
     */
    public function testGetFahrenheit($celsius, $expectedFahrenheit): void
    {
        $measurement = new Measurement();

        $measurement->setCelsius($celsius);

        $this->assertEquals($expectedFahrenheit, $measurement->getFahrenheit(), "Fahrenheit for {$celsius}°C should be {$expectedFahrenheit}°F");
    }
}
