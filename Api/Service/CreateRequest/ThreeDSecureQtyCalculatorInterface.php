<?php

namespace Worldline\PaymentCore\Api\Service\CreateRequest;

use Magento\Quote\Api\Data\CartInterface;

interface ThreeDSecureQtyCalculatorInterface
{
    public function calculateNumberOfItems(CartInterface $quote): int;
}
