<?php

namespace Worldline\PaymentCore\Service\CreateRequest;

use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\ThreeDSecureQtyCalculatorInterface;

class ThreeDSecureQtyCalculator implements ThreeDSecureQtyCalculatorInterface
{
    /**
     * @inheritDoc
     */
    public function calculateNumberOfItems(CartInterface $quote): int
    {
        $numberOfItems = 0;
        $items = $quote->getAllItems();

        if ($items === null) {
            return $numberOfItems;
        }

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $qty = (float)$item->getQty();

            if ($qty <= 0) {
                continue;
            }

            if ($qty == (int)$qty) {
                $numberOfItems += (int)$qty;
            } else {
                $numberOfItems++;
            }
        }

        return $numberOfItems;
    }
}
