<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;

/**
 * Manager interface for worldline payment entity
 */
interface PaymentDataManagerInterface
{
    /**
     * @param PaymentResponse|PaymentDetailsResponse $paymentResponse
     * @return void
     */
    public function savePaymentData($paymentResponse): void;
}
