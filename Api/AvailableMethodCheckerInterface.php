<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Payment\Gateway\Config\Config as PaymentGatewayConfig;
use Magento\Quote\Api\Data\CartInterface;

interface AvailableMethodCheckerInterface
{
    public function checkIsAvailable(PaymentGatewayConfig $config, CartInterface $quote): bool;
}
