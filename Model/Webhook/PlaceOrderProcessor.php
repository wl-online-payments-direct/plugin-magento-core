<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
use Worldline\PaymentCore\Model\AmountDiscrepancy\AmountDiscrepancyNotification;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Model\Order\ValidatorPool\DiscrepancyValidator;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
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
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\PaymentCore\Model\OrderState\OrderStateHelper;

/**
 * Identify if a webhook can trigger the order placement process, place an order and save payment information
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
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

    /**
     * @var DiscrepancyValidator
     */
    private $discrepancyValidator;

    /**
     * @var AmountDiscrepancyNotification
     */
    private $amountDiscrepancyNotification;

    /**
     * @var OrderStateHelper
     */
    private $stateHelper;

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CurrencyAmountNormalizer
     */
    private $normalizer;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    public function __construct(
        LoggerInterface $logger,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        PaymentDataManagerInterface $paymentDataManager,
        FailedOrderCreationNotification $failedOrderCreationNotification,
        PlaceOrderManagerInterface $placeOrderManager,
        SurchargingQuoteManagerInterface $surchargingQuoteManager,
        EventManager $eventManager,
        SessionDataManagerInterface $sessionDataManager,
        DiscrepancyValidator $discrepancyValidator,
        AmountDiscrepancyNotification $amountDiscrepancyNotification,
        OrderStateHelper $stateHelper,
        GeneralSettingsConfigInterface $generalSettings,
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        CurrencyAmountNormalizer $normalizer
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
        $this->discrepancyValidator = $discrepancyValidator;
        $this->amountDiscrepancyNotification = $amountDiscrepancyNotification;
        $this->stateHelper = $stateHelper;
        $this->generalSettings = $generalSettings;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param WebhooksEvent $webhookEvent
     *
     * @return void
     */
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

        $this->handleSurcharge($quote, $webhookEvent);
        $this->recalculateQuoteTotals($quote);

        try {
            if ($this->sessionDataManager->hasOrderCreationFlag($incrementId)) {
                return;
            }

            $this->sessionDataManager->setOrderCreationFlag($incrementId);
            $order = $this->quoteManagement->submit($quote);

            $this->handleOrderDiscrepancy($order);
            $this->finalizeOrderCreation($order, $quote);
        } catch (\Exception $e) {
            $this->handleOrderCreationFailure($e, $quote, $incrementId);
        }
    }

    /**
     * Handle surcharge saving if available.
     *
     * @param $quote
     * @param WebhooksEvent $webhookEvent
     *
     * @return void
     */
    private function handleSurcharge($quote, WebhooksEvent $webhookEvent): void
    {
        $payment = $webhookEvent->getPayment();
        if (!$payment) {
            return;
        }

        $surchargeSO = $payment->getPaymentOutput()->getSurchargeSpecificOutput();
        if ($surchargeSO) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeSO);
        }
    }

    /**
     * Recalculate and collect quote totals.
     *
     * @param $quote
     *
     * @return void
     */
    private function recalculateQuoteTotals($quote): void
    {
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }

    /**
     * Handle order discrepancy if detected.
     *
     * @param $order
     *
     * @return void
     */
    private function handleOrderDiscrepancy($order): void
    {
        if (!$order) {
            return;
        }

        $wlPayment = $this->discrepancyValidator->getWlPayment($order->getIncrementId());
        if (!$wlPayment || !$this->isOrderWithDiscrepancy($order)) {
            return;
        }

        $status = $this->generalSettings->getOrderDiscrepancyStatus();
        $state = $this->stateHelper->getStateByStatus($status);
        $order->setState($state)->setStatus($status);

        $orderTotals = (float)$order->getGrandTotal();
        $wlPaid = $this->normalizer->normalize((float)$wlPayment->getAmount(), $wlPayment->getCurrency());
        $difference = round($orderTotals - $wlPaid, 2);
        $currency = $order->getOrderCurrency()->getCurrencySymbol();
        $order->addCommentToStatusHistory(
            __("Warning: Order created with an amount discrepancy, order requires manual review.
                        Order Total: $orderTotals $currency,
                        Amount Paid: $wlPaid $currency,
                        Difference: $difference $currency"),
        )->setIsCustomerNotified(false);

        $this->orderRepository->save($order);

        $this->updateOrderGridStatus((int)$order->getId(), $status);
        $this->amountDiscrepancyNotification->notify($order, $wlPayment->getAmount());
    }

    /**
     * Dispatch final checkout event and clear session flag.
     *
     * @param $order
     * @param $quote
     *
     * @return void
     */
    private function finalizeOrderCreation($order, $quote): void
    {
        if (!$order) {
            return;
        }

        $this->eventManager->dispatch('checkout_submit_all_after', [
            'order' => $order,
            'quote' => $quote
        ]);
        $this->sessionDataManager->setOrderCreationFlag(null);
    }

    /**
     * Handle order creation failure and log errors.
     *
     * @param \Exception $e
     * @param $quote
     * @param string $incrementId
     *
     * @return void
     */
    private function handleOrderCreationFailure(\Exception $e, $quote, string $incrementId): void
    {
        $this->logger->error($e->getMessage(), ['reserved_order_id' => $incrementId]);
        $this->sessionDataManager->setOrderCreationFlag(null);
        $this->failedOrderCreationNotification->notify(
            $quote->getReservedOrderId(),
            $e->getMessage(),
            FailedOrderCreationNotification::WEBHOOK_SPACE
        );
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

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    private function isOrderWithDiscrepancy(OrderInterface $order): bool
    {
        return $this->discrepancyValidator->compareAmounts((float)$order->getGrandTotal(), $order->getIncrementId());
    }

    /**
     * @param int $orderId
     * @param string $status
     *
     * @return void
     */
    private function updateOrderGridStatus(int $orderId, string $status): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('sales_order_grid');

            $connection->update(
                $tableName,
                ['status' => $status],
                ['entity_id = ?' => $orderId]
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to update sales_order_grid status', [
                'entity_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
