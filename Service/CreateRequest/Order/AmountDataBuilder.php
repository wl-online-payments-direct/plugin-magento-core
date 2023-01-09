<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\AmountDataBuilderInterface;

class AmountDataBuilder implements AmountDataBuilderInterface
{
    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    public function __construct(
        AmountOfMoneyFactory $amountOfMoneyFactory
    ) {
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
    }

    public function build(CartInterface $quote): AmountOfMoney
    {
        $amountOfMoney = $this->amountOfMoneyFactory->create();

        $amount = (int)round($quote->getGrandTotal() * 100);

        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($quote->getCurrency()->getQuoteCurrencyCode());

        return $amountOfMoney;
    }
}
