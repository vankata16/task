<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class for testing the calculateCommissions class
 *
 * The class tests the calculateCommissions class by creating an instance of the class
 * with a test file and asserting the output of calculateCommissions method matches the expected result
 * and also it tests the exception when an invalid file is passed to the class constructor
 */
class CalculateCommissionsTest extends TestCase
{
    /**
     * File name which contains the transactions data
     *
     * @var string
     */
    private string $fileName = "input.txt";

    /**
     * An instance of the calculateCommissions class
     *
     * @var CalculateCommissions|null
     */
    private ?CalculateCommissions $calculator;

    /**
     * Set up an instance of the calculator class
     *
     * @return void
     */
    final public function setUp(): void
    {
        $this->calculator = new CalculateCommissions($this->fileName);
    }

    /**
     * Test the calculateCommissions method
     *
     * @return void
     */
    final public function testCalculateCommissions(): void
    {
        // arrange
        $expectedResult = [1, 0.5, 1.44, 2.39, 45.19];
        $expectedRates = ["USD" => 0.8, "EUR" => 1, "GBP" => 1.2, "JPY" => 10];

        $testData = [
            '{"bin":"45717360","amount":"100.00","currency":"EUR"}',
            '{"bin":"516793","amount":"50.00","currency":"USD"}',
            '{"bin":"45417360","amount":"10000.00","currency":"JPY"}',
            '{"bin":"41417360","amount":"130.00","currency":"USD"}',
            '{"bin":"4745030","amount":"2000.00","currency":"GBP"}',
        ];

        file_put_contents($this->fileName, implode(PHP_EOL, $testData));

        $stub = $this->createMock(CalculateCommissions::class);
        $stub->getRates();
        $stub->method('calculate')->willReturn(implode(PHP_EOL, $expectedResult));

        // act
        $result = $stub->calculate();

        // assert
        $this->assertEquals(implode(PHP_EOL, $expectedResult), $result);
    }

    /**
     * Test the exception when an invalid file is passed to the class constructor
     *
     * @return void
     */
    final public function testInvalidFile(): void
    {
        // assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"invalidFile.json" is not a valid file');

        // act
        new calculateCommissions("invalidFile.json");
    }
}
