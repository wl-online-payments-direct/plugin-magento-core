<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterfaceFactory;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceValidator;
use Worldline\PaymentCore\Model\Order\FailedOrderCreationNotification;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;

/**
 * Identify if a webhook can trigger the order placement process, place an order and save payment information
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var CanPlaceValidator
     */
    private $canPlaceValidator;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;

    /**
     * @var CanPlaceOrderContextInterfaceFactory
     */
    private $canPlaceOrderContextFactory;

    /**
     * @var FailedOrderCreationNotification
     */
    private $failedOrderCreationNotification;

    public function __construct(
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        WebhookResponseManager $webhookResponseManager,
        CanPlaceValidator $canPlaceValidator,
        PaymentDataManagerInterface $paymentDataManager,
        CanPlaceOrderContextInterfaceFactory $canPlaceOrderContextFactory,
        FailedOrderCreationNotification $failedOrderCreationNotification
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->webhookResponseManager = $webhookResponseManager;
        $this->canPlaceValidator = $canPlaceValidator;
        $this->paymentDataManager = $paymentDataManager;
        $this->canPlaceOrderContextFactory = $canPlaceOrderContextFactory;
        $this->failedOrderCreationNotification = $failedOrderCreationNotification;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        /** @var PaymentResponse $paymentResponse */
        $paymentResponse = $this->webhookResponseManager->getResponse($webhookEvent);
        // remove postfix from payment id if any (11111_1 -> 11111)
        $paymentId = (string)(int)$paymentResponse->getId();
        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($paymentId);
        if (!$quote->getId()) {
            return;
        }

        if (!$this->isValid($paymentResponse, $quote)) {
            return;
        }

        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        $this->paymentDataManager->savePaymentData($paymentResponse);

        if ($order->getId()) {
            return;
        }

        try {
            $this->quoteManagement->submit($quote);
        } catch (\Exception $e) {
            $this->failedOrderCreationNotification->notify(
                $quote->getReservedOrderId(),
                $e->getMessage(),
                FailedOrderCreationNotification::WEBHOOK_SPACE
            );
        }
    }

    private function isValid(PaymentResponse $paymentResponse, CartInterface $quote): bool
    {
        $context = $this->canPlaceOrderContextFactory->create();
        $context->setStatusCode((int)$paymentResponse->getStatusOutput()->getStatusCode());
        $context->setWorldlinePaymentId((string)$paymentResponse->getId());
        $context->setStoreId($quote->getStoreId());

        try {
            $this->canPlaceValidator->validate($context);
            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
