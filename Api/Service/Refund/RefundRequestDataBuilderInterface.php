<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Refund;

use OnlinePayments\Sdk\Domain\RefundRequest;

interface RefundRequestDataBuilderInterface
{
    public function build(float $amount, string $currencyCode): RefundRequest;
}
