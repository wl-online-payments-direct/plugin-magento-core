<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\TransactionWebhookManagerInterface;
use Worldline\PaymentCore\Api\WebhookProcessorInterface;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

class PlaceOrderProcessor implements ProcessorInterface
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var TransactionWebhookManagerInterface
     */
    private $transactionWebhookManager;

    public function __construct(
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        TransactionWebhookManagerInterface $transactionWebhookManager
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->transactionWebhookManager = $transactionWebhookManager;
    }

    public function process(WebhooksEvent $webhookEvent)
    {
        $statusCode = (int)$webhookEvent->getPayment()->getStatusOutput()->getStatusCode();
        if (!in_array(
            $statusCode,
            [
                TransactionStatusInterface::PENDING_CAPTURE_CODE,
                TransactionStatusInterface::CAPTURED_CODE,
                TransactionStatusInterface::CAPTURE_REQUESTED,
            ]
        )) {
            return;
        }

        $this->transactionWebhookManager->saveTransaction($webhookEvent);

        $orderIncrementId = (string)$webhookEvent->getPayment()
            ->getPaymentOutput()
            ->getReferences()
            ->getMerchantReference();

        $quote = $this->quoteResource->getQuoteByReservedOrderId($orderIncrementId);
        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        if ($order->getId()) {
            return;
        }

        $this->quoteManagement->placeOrder($quote->getId());
    }
}
