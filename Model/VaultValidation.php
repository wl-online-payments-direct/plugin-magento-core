<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Worldline\PaymentCore\Api\VaultValidationInterface;

class VaultValidation implements VaultValidationInterface
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    public function __construct(PaymentTokenManagementInterface $paymentTokenManagement)
    {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    public function customerHasTokensValidation(CartInterface $quote, string $paymentCode): bool
    {
        $customerId = (int)$quote->getCustomer()->getId();
        $tokens = $this->paymentTokenManagement->getListByCustomerId($customerId);

        /** @var PaymentTokenInterface $token */
        foreach ($tokens as $token) {
            if ($token->getIsActive() && $token->getIsVisible() && $token->getPaymentMethodCode() === $paymentCode) {
                return true;
            }
        }

        return false;
    }
}
