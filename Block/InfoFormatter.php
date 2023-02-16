<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block;

use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\InfoFormatterInterface;

class InfoFormatter implements InfoFormatterInterface
{
    public function format(PaymentInfoInterface $paymentInfo): array
    {
        return [
            [
                'label' => __('Total'),
                'value' => $paymentInfo->getAuthorizedAmount() . ' ' . $paymentInfo->getCurrency()
            ],
        ];
    }
}
