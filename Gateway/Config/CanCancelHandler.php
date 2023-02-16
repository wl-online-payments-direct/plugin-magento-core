<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Gateway\SubjectReader;

class CanCancelHandler implements ValueHandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Identify if a payment can be canceled
     *
     * @param array $subject
     * @param int|null $storeId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null): bool
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            return false;
        }

        if (!$payment->getAmountPaid()) {
            return true;
        }

        return $payment->getAmountPaid() < $payment->getAmountAuthorized();
    }
}
