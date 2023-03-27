<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Api\Data\CartInterface;

interface VaultValidationInterface
{
    public function customerHasTokensValidation(CartInterface $quote, string $paymentCode): bool;
}
