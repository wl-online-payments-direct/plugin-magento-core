<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;

/**
 * Assign token and customer id to the payment
 */
class TokenDataAssigner implements DataAssignerInterface
{
    public function assign(
        PaymentInterface $payment,
        QuotePaymentInterface $wlQuotePayment,
        array $additionalInformation
    ): void {
        if (!$publicToken = $additionalInformation['public_hash'] ?? false) {
            $payment->setAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH, '');
            return;
        }

        $wlQuotePayment->setPublicHash($publicToken);
        $payment->setAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH, $publicToken);
        $payment->setAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID, $payment->getQuote()->getCustomerId());
    }
}
