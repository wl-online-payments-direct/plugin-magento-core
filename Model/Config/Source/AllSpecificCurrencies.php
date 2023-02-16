<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AllSpecificCurrencies implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Currencies')],
            ['value' => 1, 'label' => __('Specific Currencies')]
        ];
    }
}
