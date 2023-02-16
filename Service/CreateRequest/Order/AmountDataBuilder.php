<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\AmountDataBuilderInterface;

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

    public function __construct(
        AmountOfMoneyFactory $amountOfMoneyFactory,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->amountFormatter = $amountFormatter;
    }

    public function build(CartInterface $quote): AmountOfMoney
    {
        $amountOfMoney = $this->amountOfMoneyFactory->create();

        $currency = (string) $quote->getCurrency()->getQuoteCurrencyCode();
        $amountOfMoney->setCurrencyCode($currency);

        $amount = $this->amountFormatter->formatToInteger((float) $quote->getGrandTotal(), $currency);
        $amountOfMoney->setAmount($amount);

        return $amountOfMoney;
    }
}
