<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;

interface DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void;
}
