<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

interface AmountFormatterInterface
{
    public function formatToInteger(float $amount, string $currency): int;

    public function formatToFloat(int $amount, string $currency): float;

    /**
     * Number of decimal places (minor units) the given currency is expressed in.
     *
     * Falls back to AmountFormatter::DEFAULT_NUMBER_OF_DECIMALS for unknown currencies.
     *
     * @param string $currency
     * @return int
     */
    public function getNumberOfDecimals(string $currency): int;
}
