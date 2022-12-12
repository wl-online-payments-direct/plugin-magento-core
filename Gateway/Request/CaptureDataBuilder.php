<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Worldline\PaymentCore\Gateway\SubjectReader;
use Worldline\PaymentCore\Service\Payment\CapturePaymentRequestBuilder;

class CaptureDataBuilder implements BuilderInterface
{
    public const CAPTURE_PAYMENT_REQUEST = 'capture_payment_request';
    public const PAYMENT_ID = 'payment_id';
    public const STORE_ID = 'store_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CapturePaymentRequestBuilder
     */
    private $capturePaymentBuilder;

    public function __construct(
        SubjectReader $subjectReader,
        CapturePaymentRequestBuilder $capturePaymentBuilder
    ) {
        $this->subjectReader = $subjectReader;
        $this->capturePaymentBuilder = $capturePaymentBuilder;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $paymentId = $payment->getCcTransId();

        if (!$paymentId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        $amount = (int) round($this->subjectReader->readAmount($buildSubject) * 100);

        return [
            self::PAYMENT_ID => $paymentId,
            self::STORE_ID => (int)$payment->getMethodInstance()->getStore(),
            self::CAPTURE_PAYMENT_REQUEST => $this->capturePaymentBuilder->build($amount),
        ];
    }
}
