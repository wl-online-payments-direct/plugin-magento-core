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

            ['AAA', 1000, 1000],
            ['AAA', -1000, -1000],
            ['AAA', 0, 0],
        ];
    }
}
