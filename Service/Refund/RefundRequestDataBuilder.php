<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Refund;

use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundRequestFactory;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Service\Refund\RefundRequestDataBuilderInterface;

class RefundRequestDataBuilder implements RefundRequestDataBuilderInterface
{
    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var RefundRequestFactory
     */
    private $refundRequestFactory;

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    public function __construct(
        AmountFormatterInterface $amountFormatter,
        RefundRequestFactory $refundRequestFactory,
        AmountOfMoneyFactory $amountOfMoneyFactory
    ) {
        $this->amountFormatter = $amountFormatter;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
    }

    public function build(float $amount, string $currencyCode): RefundRequest
    {
        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setAmount($this->amountFormatter->formatToInteger($amount, $currencyCode));
        $amountOfMoney->setCurrencyCode($currencyCode);

        $refundRequest = $this->refundRequestFactory->create();
        $refundRequest->setAmountOfMoney($amountOfMoney);

        return $refundRequest;
    }
}
