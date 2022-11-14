<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
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
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var PaymentManagerInterface
     */
    private $paymentManager;

    public function __construct(
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        WebhookResponseManager $webhookResponseManager,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        PaymentManagerInterface $paymentManager
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->webhookResponseManager = $webhookResponseManager;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->paymentManager = $paymentManager;
    }

    public function process(WebhooksEvent $webhookEvent)
    {
        /** @var PaymentResponse $paymentResponse */
        $paymentResponse = $this->webhookResponseManager->getResponse($webhookEvent);
        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
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

        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($paymentResponse->getId());
        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());

        $this->checkTransactionForSave($paymentResponse, $order);

        if ($order->getId()) {
            return;
        }

        $this->quoteManagement->placeOrder($quote->getId());
    }

    private function checkTransactionForSave(PaymentResponse $paymentResponse, Order $order): void
    {
        if (!$order->getId()) {
            $this->paymentManager->savePayment($paymentResponse);
            $this->transactionWLResponseManager->saveTransaction($paymentResponse);
        } else {
            $wlPaymentId = strtok($paymentResponse->getId(), '_');
            $orderLastTransId = strtok((string)$order->getPayment()->getLastTransId(), '_');
            if ($orderLastTransId === $wlPaymentId) {
                $this->paymentManager->savePayment($paymentResponse);
                $this->transactionWLResponseManager->saveTransaction($paymentResponse);
            }
        }
    }
}
