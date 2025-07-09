<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\OrderState;

use Worldline\PaymentCore\Api\Data\OrderStateInterfaceFactory;
use Worldline\PaymentCore\Api\OrderStateManagerInterface;

class OrderStateManager implements OrderStateManagerInterface
{
    /**
     * @var OrderStateInterfaceFactory
     */
    private $orderStateFactory;

    public function __construct(OrderStateInterfaceFactory $orderStateFactory)
    {
        $this->orderStateFactory = $orderStateFactory;
    }

    public function create(
        string $reservedOrderId,
        string $paymentCode,
        string $state,
        ?int $paymentProductId = null
    ): OrderState {
        /** @var OrderState $orderState */
        $orderState = $this->orderStateFactory->create();
        $orderState->setIncrementId($reservedOrderId);
        $orderState->setPaymentMethod($paymentCode);
        $orderState->setState($state);
        $orderState->setPaymentProductId($paymentProductId);

        return $orderState;
    }
}
