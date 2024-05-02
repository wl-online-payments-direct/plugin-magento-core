<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;

/**
 * Assigner interface for quote payment data
 */
interface DataAssignerInterface
{
    public function assign(
        PaymentInterface $payment,
        QuotePaymentInterface $wlQuotePayment,
        array $additionalInformation
    ): void;
}
