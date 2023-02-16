<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AuthorizationModeOptions implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'final',
                'label' => __('Final Authorization')
            ],
            [
                'value' => 'pre',
                'label' => __('Pre-Authorization')
            ]
        ];
    }
}
