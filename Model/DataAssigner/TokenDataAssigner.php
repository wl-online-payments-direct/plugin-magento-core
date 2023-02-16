<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Assign token and customer id to the payment
 */
class TokenDataAssigner implements DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        if (!$publicToken = $additionalInformation['public_hash'] ?? false) {
            return;
        }

        $payment->setAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH, $publicToken);
        $payment->setAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID, $payment->getQuote()->getCustomerId());
    }
}
