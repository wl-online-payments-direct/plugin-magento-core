<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

interface OrderStateInterface
{
    public function getState(): string;

    public function setState(string $state): void;

    public function getIncrementId(): string;

    public function setIncrementId(string $incrementId): void;

    public function getPaymentMethod(): string;

    public function setPaymentMethod(string $methodCode): void;

    public function getPaymentProductId(): ?int;

    public function setPaymentProductId(?int $paymentProductId = null): void;
}
