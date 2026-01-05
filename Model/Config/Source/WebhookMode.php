<?php

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WebhookMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 1, 'label' => __('Automatic')],
            ['value' => 0, 'label' => __('Manual')],
        ];
    }
}
