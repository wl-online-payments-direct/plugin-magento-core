<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

interface PendingOrderManagerInterface
{
    /**
     * @param string $incrementId
     * @return bool
     */
    public function processPendingOrder(string $incrementId): bool;
}
