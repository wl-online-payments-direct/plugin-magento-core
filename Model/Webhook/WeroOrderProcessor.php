<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Order\InvoiceManagerInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\Webhook\PlaceOrderManagerInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;

class WeroOrderProcessor implements ProcessorInterface
{
    private $orderFactory;

    private $paymentDataManager;

    private $invoiceManager;

    private $placeOrderManager;

    private $placeOrderProcessor;

    public function __construct(
        OrderFactory $orderFactory,
        PaymentDataManagerInterface $paymentDataManager,
        InvoiceManagerInterface $invoiceManager,
        PlaceOrderManagerInterface $placeOrderManager,
        PlaceOrderProcessor $placeOrderProcessor
    ) {
        $this->orderFactory = $orderFactory;
        $this->paymentDataManager = $paymentDataManager;
        $this->invoiceManager = $invoiceManager;
        $this->placeOrderManager = $placeOrderManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        $quote = $this->placeOrderManager->getValidatedQuote($webhookEvent);
        if (!$quote) {
            return;
        }

        $incrementId = (string)$quote->getReservedOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        if ($order->getId()) {
            $this->paymentDataManager->savePaymentData($webhookEvent->getPayment());
        } else {
            $this->placeOrderProcessor->process($webhookEvent);
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            if (!$order->getId()) {
                return;
            }
        }

        $this->invoiceManager->createInvoice($order);
    }
}
