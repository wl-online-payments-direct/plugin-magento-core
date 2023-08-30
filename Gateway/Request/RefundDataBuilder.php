<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Api\Service\Refund\RefundRequestDataBuilderInterface;
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
     * @var RefundRequestDataBuilderInterface
     */
    private $refundRequestBuilder;

    public function __construct(
        SubjectReader $subjectReader,
        RefundRequestDataBuilderInterface $refundRequestBuilder
    ) {
        $this->subjectReader = $subjectReader;
        $this->refundRequestBuilder = $refundRequestBuilder;
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

        $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
        if (isset($buildSubject['amount'])) {
            $amount = (float)$buildSubject['amount'];
        } else {
            $amount = (float)$this->subjectReader->readAmount($buildSubject);
        }

        return [
            self::TRANSACTION_ID => $txnId,
            self::STORE_ID => (int)$payment->getOrder()->getStoreId(),
            self::REFUND_REQUEST => $this->refundRequestBuilder->build($amount, $currencyCode)
        ];
    }
}
