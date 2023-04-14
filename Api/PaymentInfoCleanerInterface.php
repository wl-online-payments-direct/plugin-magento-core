<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Api\Data\CartInterface;

interface PaymentInfoCleanerInterface
{
    public function clean(CartInterface $quote): void;
}
