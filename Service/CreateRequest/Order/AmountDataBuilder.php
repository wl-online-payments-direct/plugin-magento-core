<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\AmountDataBuilderInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

class AmountDataBuilder implements AmountDataBuilderInterface
{
    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    public function __construct(
        AmountOfMoneyFactory $amountOfMoneyFactory,
        AmountFormatterInterface $amountFormatter,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository
    ) {
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->amountFormatter = $amountFormatter;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
    }

    public function build(CartInterface $quote): AmountOfMoney
    {
        $grandTotal = (float)$quote->getGrandTotal();
        $amountOfMoney = $this->amountOfMoneyFactory->create();

        $currency = (string) $quote->getCurrency()->getQuoteCurrencyCode();
        $amountOfMoney->setCurrencyCode($currency);

        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId((int)$quote->getId());
        $paymentMethod = str_replace('_vault', '', (string)$quote->getPayment()->getMethod());
        if ($surchargingQuote->getId() && $paymentMethod === $surchargingQuote->getPaymentMethod()) {
            $grandTotal -= (float)$surchargingQuote->getAmount();
        }

        $amount = $this->amountFormatter->formatToInteger($grandTotal, $currency);
        $amountOfMoney->setAmount($amount);

        return $amountOfMoney;
    }
}
