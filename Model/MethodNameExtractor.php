<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Quote\Model\Quote\Payment;

class MethodNameExtractor
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
        if (preg_match('~[0-9]+~', $methodName)) {
            $methodName = preg_replace('/[0-9]+/', '', $methodName);
            $methodName = rtrim($methodName, 'vault');
            $methodName = rtrim($methodName, '_');
        }

        return $methodName;
    }
}
