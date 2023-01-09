<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\PaymentResponse;

/**
 * Manager interface for worldline payment entity
 */
interface PaymentDataManagerInterface
{
    public function savePaymentData(PaymentResponse $paymentResponse): void;
}
