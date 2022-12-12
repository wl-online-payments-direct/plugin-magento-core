<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderIncrementIdChecker;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Model\ResourceModel\FailedPaymentLog;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PendingOrderManager implements PendingOrderManagerInterface
{
    /**
     * @var EmailSender
     */
    private $emailSender;

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
     * @var StatusCodeRetriever
     */
    private $statusCodeRetriever;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var FailedPaymentLog
     */
    private $failedPaymentLog;

    /**
     * @var OrderIncrementIdChecker
     */
    private $orderIncrementIdChecker;

    public function __construct(
        EmailSender $emailSender,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        StatusCodeRetriever $statusCodeRetriever,
        FailedPaymentLog $failedPaymentLog,
        OrderIncrementIdChecker $orderIncrementIdChecker
    ) {
        $this->emailSender = $emailSender;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->statusCodeRetriever = $statusCodeRetriever;
        $this->failedPaymentLog = $failedPaymentLog;
        $this->orderIncrementIdChecker = $orderIncrementIdChecker;
    }

    public function processPendingOrder(string $incrementId): bool
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            return true;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        $statusCode = $this->statusCodeRetriever->getStatusCode($quote->getPayment());

        $canPlaceOrder = in_array(
            $statusCode,
            [
                TransactionStatusInterface::PENDING_CAPTURE_CODE,
                TransactionStatusInterface::CAPTURED_CODE,
                TransactionStatusInterface::CAPTURE_REQUESTED,
            ]
        );

        if ($canPlaceOrder && !$this->orderIncrementIdChecker->isIncrementIdUsed($incrementId)) {
            $order = $this->quoteManagement->submit($quote);

            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($incrementId);
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());

            return true;
        }

        $this->checkStatusOnRefused($quote, $statusCode);

        return false;
    }

    private function checkStatusOnRefused(CartInterface $quote, ?int $statusCode): void
    {
        $isPaymentRefused = in_array(
            $statusCode,
            [
                TransactionStatusInterface::AUTHORISATION_DECLINED,
                TransactionStatusInterface::AUTHORISED_AND_CANCELLED,
                TransactionStatusInterface::PAYMENT_REFUSED
            ]
        );

        if ($isPaymentRefused && $this->emailSender->sendPaymentRefusedEmail($quote)) {
            $this->failedPaymentLog->saveQuotePaymentId((int) $quote->getPayment()->getId());
        }
    }
}
