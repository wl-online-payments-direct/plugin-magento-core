<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingQuote;

use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\QuoteTotalInterface;

class QuoteTotal implements QuoteTotalInterface
{
    public function getTotalAmount(CartInterface $quote): float
    {
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            return 0.0;
        }

        return (float)($quote->getSubtotalWithDiscount()
            + $shippingAddress->getShippingAmount()
            + $shippingAddress->getTaxAmount()
        );
    }
}
