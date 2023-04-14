<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Api\QuoteTotalInterface;

class Surcharging extends AbstractTotal
{
    public const CODE = 'worldline_payment_surcharging';

    /**
     * @var QuoteTotalInterface
     */
    private $quoteTotal;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(
        QuoteTotalInterface $quoteTotal,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository
    ) {
        $this->quoteTotal = $quoteTotal;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->setCode(self::CODE);
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): Surcharging {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        if (!$this->isApplySurcharge($quote)) {
            return $this;
        }

        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$quote->getId());

        $amount = (float)$surchargingQuote->getAmount();
        $total->setTotalAmount(self::CODE, $amount);
        $total->setBaseTotalAmount(self::CODE, $amount);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total): array
    {
        if (!$this->isApplySurcharge($quote)) {
            return [];
        }

        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$quote->getId());

        return [
            'code' => $this->getCode(),
            'title' => 'Surcharging',
            'value' => $surchargingQuote->getAmount()
        ];
    }

    private function isApplySurcharge(CartInterface $quote): bool
    {
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$quote->getId());
        if (!$surchargingQuote->getId()) {
            return false;
        }

        $paymentMethod = str_replace('_vault', '', (string)$quote->getPayment()->getMethod());
        if ($paymentMethod !== $surchargingQuote->getPaymentMethod()) {
            return false;
        }

        if ($this->quoteTotal->getTotalAmount($quote) !== (float)$surchargingQuote->getQuoteTotalAmount()) {
            return false;
        }

        return true;
    }
}
