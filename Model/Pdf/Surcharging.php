<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

class Surcharging extends DefaultTotal
{
    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    /**
     * @var SurchargingCreditmemoRepositoryInterface
     */
    private $surchargingCreditmemoRepository;

    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function getTotalsForDisplay(): array
    {
        $totals = [];
        if ($this->getSource()->getEntityType() === 'creditmemo') {
            $totals['amount'] = $this->getCreditmemoSurcharging();
        } else {
            $totals['amount'] = $this->getQuoteSurcharging();
        }

        if (empty($totals['amount'])) {
            return [];
        }

        $totals['label'] = __('Surcharging');
        $totals['font_size'] =  $this->getFontSize() ?: 7;

        return [$totals];
    }

    private function getCreditmemoSurcharging(): ?string
    {
        $creditmemoId = (int)$this->getSource()->getId();
        $surchargingCreditmemo = $this->surchargingCreditmemoRepository->getByCreditmemoId($creditmemoId);
        if (!$surchargingCreditmemo->getId()) {
            return null;
        }

        return $this->getOrder()->formatPriceTxt((float)$surchargingCreditmemo->getAmount());
    }

    private function getQuoteSurcharging(): ?string
    {
        if (!$this->getOrder()->getPayment()) {
            return null;
        }

        $quoteId = (int)$this->getOrder()->getQuoteId();
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        $paymentMethod = str_replace('_vault', '', (string)$this->getOrder()->getPayment()->getMethod());
        if (!$surchargingQuote->getId() || $paymentMethod !== $surchargingQuote->getPaymentMethod()) {
            return null;
        }

        return $this->getOrder()->formatPriceTxt((float)$surchargingQuote->getAmount());
    }
}
