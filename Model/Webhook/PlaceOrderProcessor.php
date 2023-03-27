<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
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

    public function __construct(
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        PaymentDataManagerInterface $paymentDataManager,
        FailedOrderCreationNotification $failedOrderCreationNotification,
        PlaceOrderManagerInterface $placeOrderManager,
        SurchargingQuoteManagerInterface $surchargingQuoteManager,
        EventManager $eventManager
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->paymentDataManager = $paymentDataManager;
        $this->failedOrderCreationNotification = $failedOrderCreationNotification;
        $this->placeOrderManager = $placeOrderManager;
        $this->surchargingQuoteManager = $surchargingQuoteManager;
        $this->eventManager = $eventManager;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        $quote = $this->placeOrderManager->getValidatedQuote($webhookEvent);
        if (!$quote) {
            return;
        }

        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        $this->paymentDataManager->savePaymentData($webhookEvent->getPayment());

        if ($order->getId()) {
            return;
        }

        if ($surchargeSO = $webhookEvent->getPayment()->getPaymentOutput()->getSurchargeSpecificOutput()) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeSO);
        }

        try {
            $order = $this->quoteManagement->submit($quote);
            $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
        } catch (\Exception $e) {
            $this->failedOrderCreationNotification->notify(
                $quote->getReservedOrderId(),
                $e->getMessage(),
                FailedOrderCreationNotification::WEBHOOK_SPACE
            );
        }
    }
}
