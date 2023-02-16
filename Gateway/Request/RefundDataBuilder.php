<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundRequestFactory;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Gateway\SubjectReader;

class RefundDataBuilder implements BuilderInterface
{
    public const STORE_ID = 'store_id';
    public const TRANSACTION_ID = 'transaction_id';
    public const REFUND_REQUEST = 'refund_request';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var RefundRequestFactory
     */
    private $refundRequestFactory;

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    public function __construct(
        SubjectReader $subjectReader,
        RefundRequestFactory $refundRequestFactory,
        AmountOfMoneyFactory $amountOfMoneyFactory,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->subjectReader = $subjectReader;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->amountFormatter = $amountFormatter;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        // Payment sets Capture txn id of current Invoice into ParentTransactionId Field
        $txnId = str_replace(
            ['-refund', '-capture'],
            '',
            $payment->getParentTransactionId() ?: $payment->getLastTransId()
        );

        return [
            self::TRANSACTION_ID => $txnId,
            self::STORE_ID => (int)$payment->getOrder()->getStoreId(),
            self::REFUND_REQUEST => $this->getRefundRequest($buildSubject, $payment)
        ];
    }

    private function getRefundRequest(array $buildSubject, Payment $payment): RefundRequest
    {
        $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
        $amount = $this->amountFormatter->formatToInteger(
            (float) $this->subjectReader->readAmount($buildSubject),
            (string) $currencyCode
        );

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($currencyCode);

        $refundRequest = $this->refundRequestFactory->create();
        $refundRequest->setAmountOfMoney($amountOfMoney);
        return $refundRequest;
    }
}
