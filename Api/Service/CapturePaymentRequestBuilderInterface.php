<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service;

use OnlinePayments\Sdk\Domain\CapturePaymentRequest;

interface CapturePaymentRequestBuilderInterface
{
    public function build(int $amount): CapturePaymentRequest;
}
