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
        $retriever = $this->statusCodePool->getStatusCodeRetriever($this->extractMethodName($payment));
        if (!$retriever) {
            return null;
        }

        return $retriever->getStatusCode($payment);
    }

    /**
     * Remove all unnecessary numbers and `vault` from the name, if any
     *
     * @param Payment $payment
     * @return string
     */
    private function extractMethodName(Payment $payment): string
    {
        $methodName = $payment->getMethod();
        if (preg_match('~[0-9]+~', $methodName)) {
            $methodName = preg_replace('/[0-9]+/', '', $methodName);
            $methodName = rtrim($methodName, 'vault');
            $methodName = rtrim($methodName, '_');
        }

        return $methodName;
    }
}
