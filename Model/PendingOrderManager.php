<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterfaceFactory;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceValidator;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;

/**
 * Validate payment information and create an order
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PendingOrderManager implements PendingOrderManagerInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var CanPlaceValidator
     */
    private $canPlaceValidator;

    /**
     * @var RefusedStatusProcessor
     */
    private $refusedStatusProcessor;

    /**
     * @var GetPaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;

    /**
     * @var CanPlaceOrderContextInterfaceFactory
     */
    private $canPlaceOrderContextFactory;

    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        CanPlaceValidator $canPlaceValidator,
        RefusedStatusProcessor $refusedStatusProcessor,
        GetPaymentServiceInterface $paymentService,
        PaymentDataManagerInterface $paymentDataManager,
        CanPlaceOrderContextInterfaceFactory $canPlaceOrderContextFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->canPlaceValidator = $canPlaceValidator;
        $this->refusedStatusProcessor = $refusedStatusProcessor;
        $this->paymentService = $paymentService;
        $this->paymentDataManager = $paymentDataManager;
        $this->canPlaceOrderContextFactory = $canPlaceOrderContextFactory;
    }

    public function processPendingOrder(string $incrementId): bool
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            return true;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        $paymentResponse = $this->getWlPaymentResponse($quote->getPayment());
        if (!$paymentResponse) {
            return false;
        }

        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();

        if ($this->canPlaceOrder($statusCode, $quote)) {
            $order = $this->quoteManagement->submit($quote);
            $this->paymentDataManager->savePaymentData($paymentResponse);

            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($incrementId);
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());

            return true;
        }

        $this->refusedStatusProcessor->process($quote, $statusCode);

        return false;
    }

    private function getWlPaymentResponse(PaymentInterface $payment): ?PaymentResponse
    {
        $wlPaymentId = ((int)$payment->getAdditionalInformation('payment_id') . '_0');
        $storeId = (int)$payment->getMethodInstance()->getStore();

        try {
            return $this->paymentService->execute($wlPaymentId, $storeId);
        } catch (LocalizedException $e) {
            return null;
        }
    }

    private function canPlaceOrder(int $statusCode, $quote): bool
    {
        $wlPaymentId = (string)$quote->getPayment()->getAdditionalInformation('payment_id');
        $context = $this->canPlaceOrderContextFactory->create();
        $context->setStatusCode($statusCode);
        $context->setWorldlinePaymentId($wlPaymentId);
        $context->setIncrementId($quote->getReservedOrderId());
        $context->setStoreId($quote->getStoreId());

        try {
            $this->canPlaceValidator->validate($context);
            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
