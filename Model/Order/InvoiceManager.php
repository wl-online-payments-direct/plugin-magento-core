<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Order\InvoiceManagerInterface;

class InvoiceManager implements InvoiceManagerInterface
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        InvoiceRepositoryInterface $invoiceRepository,
        LoggerInterface $logger
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceRepository = $invoiceRepository;
        $this->logger = $logger;
    }

    public function createInvoice(OrderInterface $order): void
    {
        if (!$order->canInvoice()) {
            return;
        }

        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->setState(Invoice::STATE_PAID);
            $this->invoiceRepository->save($invoice);

            $this->transaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $this->invoiceSender->send($invoice);

            $order->addCommentToStatusHistory(
                __('The customer is notified about invoice creation #%1.', $invoice->getId())
            )->setIsCustomerNotified(true)->save();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
