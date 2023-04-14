<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Api\Data\CartInterface;

interface QuoteTotalInterface
{
    public function getTotalAmount(CartInterface $quote): float;
}
