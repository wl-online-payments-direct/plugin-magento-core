<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingQuote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\SurchargeSpecificOutput;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\QuoteTotalInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteManagerInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

class SurchargingQuoteManager implements SurchargingQuoteManagerInterface
{
    /**
     * @var QuoteTotalInterface
     */
    private $quoteTotal;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(
        QuoteTotalInterface $quoteTotal,
        PriceCurrencyInterface $priceCurrency,
        AmountFormatterInterface $amountFormatter,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository
    ) {
        $this->quoteTotal = $quoteTotal;
        $this->priceCurrency = $priceCurrency;
        $this->amountFormatter = $amountFormatter;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
    }

    public function saveSurchargingQuote(CartInterface $quote, float $surcharging): void
    {
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$quote->getId());
        $surchargingQuote->setQuoteId((int)$quote->getId());
        // the quota contains the `_vault` | the order doesn't contain the `_vault`
        $paymentMethod = str_replace('_vault', '', (string)$quote->getPayment()->getMethod());
        $surchargingQuote->setPaymentMethod($paymentMethod);
        $surchargingQuote->setAmount($surcharging);
        $surchargingQuote->setBaseAmount($this->priceCurrency->convertAndRound($surcharging));
        $surchargingQuote->setQuoteTotalAmount($this->quoteTotal->getTotalAmount($quote));

        $this->surchargingQuoteRepository->save($surchargingQuote);
    }

    public function formatAndSaveSurchargingQuote(CartInterface $quote, SurchargeSpecificOutput $surchargeOutput): void
    {
        $amount = $surchargeOutput->getSurchargeAmount()->getAmount();
        $currency = $surchargeOutput->getSurchargeAmount()->getCurrencyCode();
        $surchargeAmount = $this->amountFormatter->formatToFloat($amount, $currency);
        $this->saveSurchargingQuote($quote, $surchargeAmount);
    }
}
