<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

interface SurchargingCreditmemoManagerInterface
{
    public function createSurcharging(int $creditmemoId, int $quoteId, float $surchargingAmount): void;
}
