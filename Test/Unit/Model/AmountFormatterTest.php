<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Worldline\PaymentCore\Model\AmountFormatter;

class AmountFormatterTest extends TestCase
{
    /**
     * @var AmountFormatter
     */
    private $amountFormatter;

    protected function setUp(): void
    {
        $this->amountFormatter = new AmountFormatter;
    }

    /**
     * @param string $currency
     * @param int $amount
     * @param float $expected
     * @return void
     *
     * @dataProvider dataProviderFormat
     */
    public function testFormatToFloat(string $currency, int $amount, float $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->amountFormatter->formatToFloat($amount, $currency)
        );
    }

    /**
     * @param string $currency
     * @param int $expected
     * @param float $amount
     * @return void
     *
     * @dataProvider dataProviderFormat
     */
    public function testFormatToInt(string $currency, int $expected, float $amount): void
    {
        $this->assertEquals(
            $expected,
            $this->amountFormatter->formatToInteger($amount, $currency)
        );
    }

    /**
     * @param string $currency
     * @param int $expected
     * @return void
     *
     * @dataProvider dataProviderNumberOfDecimals
     */
    public function testGetNumberOfDecimals(string $currency, int $expected): void
    {
        $this->assertSame(
            $expected,
            $this->amountFormatter->getNumberOfDecimals($currency)
        );
    }

    public function dataProviderNumberOfDecimals(): array
    {
        return [
            ['EUR', 2],
            ['USD', 2],
            ['AUD', 2],
            ['JPY', 0],
            ['IQD', 3],
            ['CLF', 4],
            ['UYW', 4],

            // redenominated currencies, their retired predecessors are still part of the mapping
            ['VES', 2],
            ['SLE', 2],
            ['STN', 2],
            ['XCG', 2],
            ['ZWG', 2],

            // unknown currencies fall back to the ISO 4217 default
            ['AAA', 2],
            ['', 2],
        ];
    }

    public function dataProviderFormat(): array
    {
        return [
            ['JPY', 1000, 1000],
            ['JPY', -1000, -1000],
            ['JPY', 0, 0],

            ['USD', 10, 0.1],
            ['USD', 1000, 10],
            ['USD', -1000, -10],
            ['USD', 0, 0.00],

            ['CLF', 10, 0.001],
            ['CLF', 1000, 0.1],
            ['CLF', -1000, -0.1],
            ['CLF', 0, 0.0000],

            ['IQD', 10, 0.01],
            ['IQD', 1000, 1],
            ['IQD', -1000, -1],
            ['IQD', 0, 0.000],

            ['VES', 17990, 179.90],
            ['ZWG', -17990, -179.90],
            ['UYW', 1000, 0.1],

            ['AAA', 1000, 10],
            ['AAA', -1000, -10],
            ['AAA', 0, 0],
        ];
    }
}
