<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Exception;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteManagerInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceOrderContextManager;
use Worldline\PaymentCore\Model\PaymentOrderManager\PaymentService;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

/**
 * Validate payment information and create an order
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class PendingOrderManager implements PendingOrderManagerInterface
{
    /**
     * @var SessionDataManagerInterface
     */
    private $sessionDataManager;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var CanPlaceOrderContextManager
     */
    private $canPlaceOrderContextManager;

    /**
     * @var RefusedStatusProcessor
     */
    private $refusedStatusProcessor;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;

    /**
     * @var SurchargingQuoteManagerInterface
     */
    private $surchargingQuoteManager;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SessionDataManagerInterface $sessionDataManager,
        OrderFactory $orderFactory,
        QuoteResourceInterface $quoteResource,
        QuoteManagement $quoteManagement,
        CanPlaceOrderContextManager $canPlaceOrderContextManager,
        RefusedStatusProcessor $refusedStatusProcessor,
        PaymentService $paymentService,
        PaymentDataManagerInterface $paymentDataManager,
        SurchargingQuoteManagerInterface $surchargingQuoteManager,
        EventManager $eventManager,
        LoggerInterface $logger
    ) {
        $this->sessionDataManager = $sessionDataManager;
        $this->orderFactory = $orderFactory;
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->canPlaceOrderContextManager = $canPlaceOrderContextManager;
        $this->refusedStatusProcessor = $refusedStatusProcessor;
        $this->paymentService = $paymentService;
        $this->paymentDataManager = $paymentDataManager;
        $this->surchargingQuoteManager = $surchargingQuoteManager;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    public function processPendingOrder(string $incrementId): bool
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            return true;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote) {
            return false;
        }

        $payment = $quote->getPayment();
        if (!$payment->getAdditionalInformation('payment_id')) {
            $paymentIds = (array)$payment->getAdditionalInformation('payment_ids');
            $payment->setAdditionalInformation('payment_id', end($paymentIds));
            $this->quoteResource->save($quote);
        }

        $paymentResponse = $this->paymentService->getPaymentResponse($quote->getPayment());
        if (!$paymentResponse) {
            return false;
        }

        if ($surchargeSO = $paymentResponse->getPaymentOutput()->getSurchargeSpecificOutput()) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeSO);
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
        if ($statusCode === TransactionStatusInterface::WAITING_AUTHENTICATION) {
            return true;
        }

        $context = $this->canPlaceOrderContextManager->createContext($quote, $statusCode);
        if ($this->canPlaceOrderContextManager->canPlaceOrder($context)) {
            $this->paymentDataManager->savePaymentData($paymentResponse);
            if ($this->sessionDataManager->hasOrderCreationFlag($incrementId)) {
                return true;
            }
            $this->sessionDataManager->setOrderCreationFlag($incrementId);

            try {
                $order = $this->quoteManagement->submit($quote);
                if (!$order) {
                    $this->refusedStatusProcessor->process($quote, $statusCode);
                    return false;
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), ['reserved_order_id' => $incrementId]);
                $this->refusedStatusProcessor->process($quote, $statusCode);
                return false;
            }

            $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
            $this->sessionDataManager->setOrderData($order);
            $this->sessionDataManager->setOrderCreationFlag(null);

            return true;
        }

        $this->refusedStatusProcessor->process($quote, $statusCode);

        return false;
    }
}
