<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Gateway\SubjectReader;

/**
 * Handle cancel and void responses
 */
class CancelAndVoidHandler implements HandlerInterface
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
     * Handle cancel and void responses
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            $orderPayment = $paymentDO->getPayment();
            $orderPayment->setIsTransactionClosed(true);
            $orderPayment->setShouldCloseParentTransaction(true);
        }
    }
}
