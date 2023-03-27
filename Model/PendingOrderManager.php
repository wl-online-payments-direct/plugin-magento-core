<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteManagerInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceOrderContextManager;
use Worldline\PaymentCore\Model\PaymentOrderManager\PaymentService;

/**
 * Validate payment information and create an order
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
     * @param SessionDataManagerInterface $sessionDataManager
     * @param OrderFactory $orderFactory
     * @param QuoteResourceInterface $quoteResource
     * @param QuoteManagement $quoteManagement
     * @param CanPlaceOrderContextManager $canPlaceOrderContextManager
     * @param RefusedStatusProcessor $refusedStatusProcessor
     * @param PaymentService $paymentService
     * @param PaymentDataManagerInterface $paymentDataManager
     * @param SurchargingQuoteManagerInterface $surchargingQuoteManager
     * @param EventManager $eventManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
        EventManager $eventManager
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
    }

    public function processPendingOrder(string $incrementId): bool
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            return true;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        $paymentResponse = $this->paymentService->getPaymentResponse($quote->getPayment());
        if (!$paymentResponse) {
            return false;
        }

        if ($surchargeSO = $paymentResponse->getPaymentOutput()->getSurchargeSpecificOutput()) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeSO);
        }

        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
        $context = $this->canPlaceOrderContextManager->createContext($quote, $statusCode);
        if ($this->canPlaceOrderContextManager->canPlaceOrder($context)) {
            $this->paymentDataManager->savePaymentData($paymentResponse);
            $order = $this->quoteManagement->submit($quote);
            $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);

            $this->sessionDataManager->setOrderData($order);

            return true;
        }

        $this->refusedStatusProcessor->process($quote, $statusCode);

        return false;
    }
}
