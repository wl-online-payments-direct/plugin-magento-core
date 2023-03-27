<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\SurchargingQuoteInterface;

interface SurchargingQuoteRepositoryInterface
{
    public function save(SurchargingQuoteInterface $surchargingQuoteEntity): SurchargingQuoteInterface;

    public function getByQuoteId(int $quoteId): SurchargingQuoteInterface;

    public function getByOrderId(int $orderId): SurchargingQuoteInterface;

    public function deleteByQuoteId(int $quoteId): void;
}
