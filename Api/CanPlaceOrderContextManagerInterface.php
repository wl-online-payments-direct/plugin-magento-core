<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterface;

interface CanPlaceOrderContextManagerInterface
{
    public function createContext(CartInterface $quote, int $statusCode): CanPlaceOrderContextInterface;

    public function canPlaceOrder(CanPlaceOrderContextInterface $context): bool;
}
