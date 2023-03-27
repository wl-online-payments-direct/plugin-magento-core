<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Sales\Invoice\View;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Model\Quote\Surcharging as QuoteSurcharging;

class Surcharging extends Template
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
    }

    public function initTotals(): Surcharging
    {
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        $invoice = $parent->getInvoice();
        $paymentMethod = str_replace('_vault', '', (string)$order->getPayment()->getMethod());
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$order->getQuoteId());
        if (!$surchargingQuote->getId() || $paymentMethod !== $surchargingQuote->getPaymentMethod()) {
            return $this;
        }

        if ((int)$surchargingQuote->getInvoiceId() !== (int)$invoice->getId()) {
            return $this;
        }

        $total = [
            'code' => QuoteSurcharging::CODE,
            'strong' => false,
            'value' => $surchargingQuote->getAmount(),
            'label' => __('Surcharging'),
        ];

        $surcharging = $this->dataObjectFactory->create()->setData($total);

        $parent->addTotal($surcharging, 'shipping');

        return $this;
    }
}
