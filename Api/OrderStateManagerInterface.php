<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Model\OrderState\OrderState;

interface OrderStateManagerInterface
{
    public function create(
        string $reservedOrderId,
        string $paymentCode,
        string $state,
        ?int $paymentProductId = null
    ): OrderState;
}
