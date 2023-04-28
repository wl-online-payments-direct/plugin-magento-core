<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Gateway\SubjectReader;

class RefundHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handle refund creation process
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if (!$paymentDO->getPayment() instanceof Payment) {
            return;
        }

        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
        $closed = $this->shouldCloseParentTransaction($orderPayment);
        $orderPayment->setShouldCloseParentTransaction($closed);
    }

    protected function shouldCloseTransaction(): bool
    {
        return true;
    }

    protected function shouldCloseParentTransaction(Payment $orderPayment): bool
    {
        if (!$orderPayment->getCreditmemo() || !$orderPayment->getCreditmemo()->getInvoice()) {
            return false;
        }

        return !$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
