<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Observer\Sales\Model\Order\Invoice;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

/**
 * Save surcharging data when submit invoice, 'sales_order_invoice_save_after' event
 */
class IsInvoicedSurcharging implements ObserverInterface
{
    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(SurchargingQuoteRepositoryInterface $surchargingQuoteRepository)
    {
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
    }

    public function execute(Observer $observer): void
    {
        $invoice = $observer->getEvent()->getInvoice();
        $quoteId = (int)$invoice->getOrder()->getQuoteId();
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        if (!$surchargingQuote->getId()) {
            return;
        }

        if ($surchargingQuote->getIsInvoiced()) {
            return;
        }

        $surchargingQuote->setIsInvoiced(true);
        $surchargingQuote->setInvoiceId((int)$invoice->getId());

        $this->surchargingQuoteRepository->save($surchargingQuote);
    }
}
