<?php

namespace Worldline\PaymentCore\Controller\Adminhtml\Order;

use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

class Accept extends Action
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
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var CurrencyAmountNormalizer
     */
    private $currencyAmountNormalizer;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        CurrencyAmountNormalizer $currencyAmountNormalizer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->currencyAmountNormalizer = $currencyAmountNormalizer;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $paidAmount = (float)$this->getRequest()->getParam('paid_amount');
        $statusCode = $this->getRequest()->getParam('status_code');
        $transactionId = $this->getRequest()->getParam('transaction_id');

        $this->logger->info('Order with amount discrepancy accepted', [
            'order_id' => $orderId
        ]);

        try {
            $order = $this->orderRepository->get($orderId);

            // handle invoice creation for the holded order
            if ($order->canUnhold()) {
                $order->unhold();
            }

            if ((int)$statusCode === TransactionStatusInterface::CAPTURED_CODE) {
                if (!$order->canInvoice()) {
                    return $result->setData([
                        'success' => false,
                        'message' => __('Order in status ' . $order->getStatus() .  ' cannot be invoiced.')
                    ]);
                }

                // Prepare invoice normally
                $invoice = $this->invoiceService->prepareInvoice($order);

                $shippingAmount = (float)$order->getShippingAmount();
                $subtotal = $paidAmount - $shippingAmount;

                $invoice->setSubtotal($subtotal);
                $invoice->setBaseSubtotal($subtotal);
                $invoice->setGrandTotal($paidAmount);
                $invoice->setBaseGrandTotal($paidAmount);

                $invoice->register();
                $invoice->pay();

                // Add capture transaction to allow online refund
                $payment = $order->getPayment();
                $payment->setTransactionId($transactionId . $invoice->getId())
                    ->setIsTransactionClosed(1)
                    ->addTransaction(PaymentTransaction::TYPE_CAPTURE, $invoice);

                $this->transaction->addObject($invoice)
                    ->addObject($payment);
            }

            // Add order comment about the discrepancy
            $order->addCommentToStatusHistory(
                __("Order review accepted. Amount discrepancy acknowledged.", $paidAmount)
            )->setIsCustomerNotified(false);

            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(Order::STATE_PROCESSING);

            // Add discrepancy accepted flag to the order
            $order->setData('discrepancy_accepted', 1);

            // Save order (and invoice/payment) in transaction
            $this->transaction
                ->addObject($order)
                ->save();

            return $result->setData([
                'success' => true,
                'message' => __('Invoice created successfully with amount %1.', $paidAmount)
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
