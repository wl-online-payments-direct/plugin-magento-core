<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface SessionDataManagerInterface
{
    public function setOrderData(OrderInterface $order): void;

    public function reserveOrder(string $reservedOrderId): void;

    public function setOrderCreationFlag(?string $reservedOrderId): void;

    public function hasOrderCreationFlag(string $reservedOrderId): bool;
}
