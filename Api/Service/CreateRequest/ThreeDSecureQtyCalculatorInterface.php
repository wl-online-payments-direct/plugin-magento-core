<?php

namespace Worldline\PaymentCore\Api\Service\CreateRequest;

use Magento\Quote\Api\Data\CartInterface;

interface ThreeDSecureQtyCalculatorInterface
{
    /**
     * Calculate number of items.
     *
     * @param CartInterface $quote
     *
     * @return int
     */
    public function calculateNumberOfItems(CartInterface $quote): int;
}
