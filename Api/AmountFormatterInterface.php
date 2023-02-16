<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

interface AmountFormatterInterface
{
    public function formatToInteger(float $amount, string $currency): int;

    public function formatToFloat(int $amount, string $currency): float;
}
