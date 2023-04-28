<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

class Surcharging extends AbstractTotal
{
    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
    }

    public function collect(Invoice $invoice): Surcharging
    {
        $order = $invoice->getOrder();
        if (!$order->getPayment()) {
            return $this;
        }

        $quoteId = (int)$order->getQuoteId();
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        $paymentMethod = str_replace('_vault', '', (string)$order->getPayment()->getMethod());
        if (!$surchargingQuote->getId() || $paymentMethod !== $surchargingQuote->getPaymentMethod()) {
            return $this;
        }

        if ($surchargingQuote->getIsInvoiced()) {
            return $this;
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $surchargingQuote->getAmount());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $surchargingQuote->getBaseAmount());

        return $this;
    }
}
