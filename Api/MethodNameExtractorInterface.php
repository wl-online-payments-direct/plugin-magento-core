<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Quote\Model\Quote\Payment;

interface MethodNameExtractorInterface
{
    public function extract(Payment $payment): string;
}
