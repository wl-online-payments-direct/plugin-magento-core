<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Quote\Model\Quote\Payment;
use Worldline\PaymentCore\Model\PaymentStatusCode\StatusCodePool;

class StatusCodeRetriever
{
    /**
     * @var StatusCodePool
     */
    private $statusCodePool;

    public function __construct(StatusCodePool $statusCodePool)
    {
        $this->statusCodePool = $statusCodePool;
    }

    public function getStatusCode(Payment $payment): ?int
    {
        $retriever = $this->statusCodePool->getStatusCodeRetriever($payment->getMethod());
        if (!$retriever) {
            return null;
        }

        return $retriever->getStatusCode($payment);
    }
}
