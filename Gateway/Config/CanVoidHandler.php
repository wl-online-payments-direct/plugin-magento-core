<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Gateway\SubjectReader;

class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var GetPaymentServiceInterface
     */
    private $getPaymentService;

    public function __construct(SubjectReader $subjectReader, GetPaymentServiceInterface $getPaymentService)
    {
        $this->subjectReader = $subjectReader;
        $this->getPaymentService = $getPaymentService;
    }

    /**
     * Identify if a payment can be voided or canceled
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return bool
     */
    public function handle(array $subject, $storeId = null): bool
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            return false;
        }

        $transactionId = $payment->getParentTransactionId() ?: $payment->getLastTransId();

        try {
            $wlPayment = $this->getPaymentService->execute($transactionId, (int) $storeId);

            return $wlPayment->getStatusOutput()->getIsCancellable() && !$payment->getAmountPaid();
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
