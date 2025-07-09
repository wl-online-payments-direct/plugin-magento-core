<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\OrderState;

use Worldline\PaymentCore\Api\Data\OrderStateInterface;

class OrderState implements OrderStateInterface
{
    /**
     * @var string
     */
    private $state = '';

    /**
     * @var string
     */
    private $incrementId = '';

    /**
     * @var string
     */
    private $methodCode = '';

    /**
     * @var int|null
     */
    private $paymentProductId;

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getIncrementId(): string
    {
        return $this->incrementId;
    }

    public function setIncrementId(string $incrementId): void
    {
        $this->incrementId = $incrementId;
    }

    public function getPaymentMethod(): string
    {
        return $this->methodCode;
    }

    public function setPaymentMethod(string $methodCode): void
    {
        $this->methodCode = $methodCode;
    }

    public function getPaymentProductId(): ?int
    {
        return $this->paymentProductId;
    }

    public function setPaymentProductId(?int $paymentProductId = null): void
    {
        $this->paymentProductId = $paymentProductId;
    }
}
