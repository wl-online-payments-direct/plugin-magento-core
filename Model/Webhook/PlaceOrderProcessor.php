<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteManagerInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;
use Worldline\PaymentCore\Model\Order\FailedOrderCreationNotification;
use Worldline\PaymentCore\Api\Webhook\PlaceOrderManagerInterface;

/**
 * Identify if a webhook can trigger the order placement process, place an order and save payment information
 */
class PlaceOrderProcessor implements ProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;

    /**
     * @var FailedOrderCreationNotification
     */
    private $failedOrderCreationNotification;

    /**
     * @var PlaceOrderManagerInterface
     */
    private $placeOrderManager;

    /**
     * @var SurchargingQuoteManagerInterface
     */
    private $surchargingQuoteManager;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var SessionDataManagerInterface
     */
    private $sessionDataManager;

    public function __construct(
        LoggerInterface $logger,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        PaymentDataManagerInterface $paymentDataManager,
        FailedOrderCreationNotification $failedOrderCreationNotification,
        PlaceOrderManagerInterface $placeOrderManager,
        SurchargingQuoteManagerInterface $surchargingQuoteManager,
        EventManager $eventManager,
        SessionDataManagerInterface $sessionDataManager
    ) {
        $this->logger = $logger;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->paymentDataManager = $paymentDataManager;
        $this->failedOrderCreationNotification = $failedOrderCreationNotification;
        $this->placeOrderManager = $placeOrderManager;
        $this->surchargingQuoteManager = $surchargingQuoteManager;
        $this->eventManager = $eventManager;
        $this->sessionDataManager = $sessionDataManager;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        if (!$this->shouldHandleEvent($webhookEvent)) {
            return;
        }
        $quote = $this->placeOrderManager->getValidatedQuote($webhookEvent);
        if (!$quote) {
            return;
        }

        $incrementId = (string)$quote->getReservedOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        $this->paymentDataManager->savePaymentData($webhookEvent->getPayment());

        if ($order->getId() || !$webhookEvent->getPayment()) {
            return;
        }

        if ($surchargeSO = $webhookEvent->getPayment()->getPaymentOutput()->getSurchargeSpecificOutput()) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeSO);
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        try {
            if ($this->sessionDataManager->hasOrderCreationFlag($incrementId)) {
                return;
            }
            $this->sessionDataManager->setOrderCreationFlag($incrementId);

            $order = $this->quoteManagement->submit($quote);
            if (!$order) {
                return;
            }

            $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
            $this->sessionDataManager->setOrderCreationFlag(null);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['reserved_order_id' => $incrementId]);
            $this->sessionDataManager->setOrderCreationFlag(null);
            $this->failedOrderCreationNotification->notify(
                $quote->getReservedOrderId(),
                $e->getMessage(),
                FailedOrderCreationNotification::WEBHOOK_SPACE
            );
        }
    }

    /**
     * Determine if the event should be handled.
     *
     * @param WebhooksEvent $event
     *
     * @return bool
     */
    private function shouldHandleEvent(WebhooksEvent $event): bool
    {
        $paymentProductId = $this->getPaymentProductId($event);

        if ($this->isVoucherProduct($paymentProductId)) {
            return $this->hasEqualAmounts($event);
        }

        return true;
    }

    /**
     * Get payment product ID from event.
     *
     * @param WebhooksEvent $event
     *
     * @return int|null
     */
    private function getPaymentProductId(WebhooksEvent $event): ?int
    {
        $payment = $event->getPayment();
        if (!$payment) {
            return null;
        }

        $paymentOutput = $payment->getPaymentOutput();
        if (!$paymentOutput) {
            return null;
        }

        $redirectOutput = $paymentOutput->getRedirectPaymentMethodSpecificOutput();
        if (!$redirectOutput) {
            return null;
        }

        return $redirectOutput->getPaymentProductId();
    }

    /**
     * Check if amounts are equal for voucher products.
     *
     * @param WebhooksEvent $event
     *
     * @return bool
     */
    private function hasEqualAmounts(WebhooksEvent $event): bool
    {
        $payment = $event->getPayment();
        if (!$payment) {
            return false;
        }

        $paymentOutput = $payment->getPaymentOutput();
        if (!$paymentOutput) {
            return false;
        }

        $amountOfMoney = $paymentOutput->getAmountOfMoney()
            ? $paymentOutput->getAmountOfMoney()->getAmount()
            : null;

        $acquiredAmount = $paymentOutput->getAcquiredAmount()
            ? $paymentOutput->getAcquiredAmount()->getAmount()
            : null;

        return $amountOfMoney && $acquiredAmount && ($amountOfMoney === $acquiredAmount);
    }

    /**
     * Check if the payment product is a voucher type.
     *
     * @param int|null $paymentProductId
     *
     * @return bool
     */
    private function isVoucherProduct(?int $paymentProductId): bool
    {
        return in_array($paymentProductId, [
            PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID,
            PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID
        ], true);
    }
}
