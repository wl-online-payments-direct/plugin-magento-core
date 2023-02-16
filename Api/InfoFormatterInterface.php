<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;

interface InfoFormatterInterface
{
    public function format(PaymentInfoInterface $paymentInfo): array;
}
