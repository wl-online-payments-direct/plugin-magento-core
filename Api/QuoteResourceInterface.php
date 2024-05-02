<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Api\Data\CartInterface;

interface QuoteResourceInterface
{
    public function getQuoteByReservedOrderId(string $reservedOrderId): ?CartInterface;

    public function getQuoteByWorldlinePaymentId(string $paymentId): ?CartInterface;

    public function setPaymentIdAndSave(CartInterface $quote, int $paymentProductId): void;

    public function save(CartInterface $quote): void;
}
