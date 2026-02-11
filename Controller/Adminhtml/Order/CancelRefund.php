<?php

namespace Worldline\PaymentCore\Controller\Adminhtml\Order;

use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Service\Payment\CancelPaymentService;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DB\Transaction;
use Worldline\PaymentCore\Service\Refund\CreateRefundService;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\PaymentOutput;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundRequest;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelRefund extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var CreateRefundService
     */
    protected $createRefundService;
    /**
     * @var CancelPaymentService
     */
    protected $cancelPaymentService;
    /**
     * @var CurrencyAmountNormalizer
     */
    protected $currencyNormalizer;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        CreateRefundService $createRefundService,
        CancelPaymentService $cancelPaymentService,
        CurrencyAmountNormalizer $currencyNormalizer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->createRefundService = $createRefundService;
        $this->cancelPaymentService = $cancelPaymentService;
        $this->currencyNormalizer = $currencyNormalizer;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $paidAmount = (float)$this->getRequest()->getParam('paid_amount');
        $currency = $this->getRequest()->getParam('currency');
        $transactionId = $this->getRequest()->getParam('transaction_id');

        $this->logger->info('Order with amount discrepancy cancelled and refunded', [
            'order_id' => $orderId,
            'refunded_amount' => $paidAmount
        ]);

        try {
            $order = $this->orderRepository->get($orderId);

            if ($order->canUnhold()) {
                $order->unhold();
            }

            if (!$order->canCancel()) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Order in status ' . $order->getStatus() . ' cannot be canceled.')
                ]);
            }

            // Attempt to cancel the payment. If cancellation fails, try to refund it instead.
            try {
                $cancelRequest = $this->createCancelRequest($transactionId, $paidAmount, $currency);
                $this->cancelPaymentService->execute($cancelRequest, $order->getStoreId());
            } catch (\Exception $exception) {
                $refundRequest = $this->createRefundRequest($paidAmount, $currency);
                $this->createRefundService->execute($transactionId, $refundRequest, $order->getStoreId());
            }

            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);

            // Add order comment about the discrepancy rejection
            $order->addCommentToStatusHistory(
                __("Order cancelled and fully refunded due to amount discrepancy.")
            )->setIsCustomerNotified(false);

            // Add discrepancy rejected flag to the order
            $order->setData('discrepancy_rejected', 1);

            $this->transaction->addObject($order)->save();

            return $result->setData([
                'success' => true,
                'message' => __('Order cancelled and fully refunded.')
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param float $paidAmount
     * @param string $currency
     *
     * @return RefundRequest
     */
    private function createRefundRequest($paidAmount, $currency)
    {
        $refundRequest = new RefundRequest();
        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setAmount($this->currencyNormalizer->normalize((float)$paidAmount, $currency, true));
        $amountOfMoney->setCurrencyCode($currency);
        $refundRequest->setAmountOfMoney($amountOfMoney);

        return $refundRequest;
    }

    /**
     * @param string $paymentId
     * @param float $paidAmount
     * @param string $currency
     *
     * @return PaymentResponse
     */
    private function createCancelRequest($paymentId, $paidAmount, $currency)
    {
        $paymentResponse = new PaymentResponse();
        $paymentOutput = new PaymentOutput();
        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setAmount($this->currencyNormalizer->normalize((float)$paidAmount, $currency, true));
        $amountOfMoney->setCurrencyCode($currency);
        $paymentOutput->setAmountOfMoney($amountOfMoney);
        $paymentResponse->setPaymentOutput($paymentOutput);
        $paymentResponse->setId($paymentId);

        return $paymentResponse;
    }
}
