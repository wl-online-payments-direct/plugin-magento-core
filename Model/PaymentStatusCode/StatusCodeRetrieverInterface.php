<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\PaymentStatusCode;

use Magento\Quote\Model\Quote\Payment;

interface StatusCodeRetrieverInterface
{
    public function getStatusCode(Payment $payment): ?int;
}
