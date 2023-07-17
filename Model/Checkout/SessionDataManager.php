<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SessionDataManager implements SessionDataManagerInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function setOrderData(OrderInterface $order): void
    {
        $this->checkoutSession->setLastOrderId((int) $order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
    }

    public function reserveOrder(string $reservedOrderId): void
    {
        $this->checkoutSession->clearStorage();
        $this->checkoutSession->setLastRealOrderId($reservedOrderId);
    }

    public function setOrderCreationFlag(?string $reservedOrderId): void
    {
        $this->checkoutSession->setOrderCreationFlag($reservedOrderId);
    }

    public function hasOrderCreationFlag(string $reservedOrderId): bool
    {
        return (bool)$this->checkoutSession->getOrderCreationFlag($reservedOrderId);
    }
}
