<?php

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status as MagentoOrderStatus;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderStatus extends MagentoOrderStatus
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderConfig $orderConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderConfig $orderConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($orderConfig);
    }

    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        $defaultReviewStatus = 'holded';

        foreach ($options as &$option) {
            if ($option['value'] === $defaultReviewStatus) {
                $option['label'] = $option['label'] . ' (Default)';
            }
        }

        return array_filter($options, function ($option) {
            return $option['value'] !== '';
        });
    }
}
