<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BankTransferModes implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 1,
                'label' => __('Instant Payments Only')
            ],
            [
                'value' => 0,
                'label' => __('Standard & Instant Payments')
            ]
        ];
    }
}
