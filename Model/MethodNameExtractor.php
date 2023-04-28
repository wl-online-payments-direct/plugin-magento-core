<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Quote\Model\Quote\Payment;
use Worldline\PaymentCore\Api\MethodNameExtractorInterface;

class MethodNameExtractor implements MethodNameExtractorInterface
{
    /**
     * Remove all unnecessary numbers and `vault` from the name, if any
     *
     * @param Payment $payment
     * @return string
     */
    public function extract(Payment $payment): string
    {
        $methodName = $payment->getMethod();
        if (preg_match('~\d+~', $methodName)) {
            $methodName = preg_replace('/\d+/', '', $methodName);
            $methodName = rtrim($methodName, 'vault');
            $methodName = rtrim($methodName, '_');
        }

        return $methodName;
    }
}
