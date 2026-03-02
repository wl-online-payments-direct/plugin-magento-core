<?php

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status as MagentoOrderStatus;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
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

    private function setStateStatuses()
    {
        $this->_stateStatuses = [Order::STATE_PAYMENT_REVIEW];
    }

    public function toOptionArray()
    {
        $this->setStateStatuses();

        $options = parent::toOptionArray();

        $defaultReviewStatus = 'payment_review';

        $head = [];
        $tail = [];

        foreach ($options as $option) {
            if ($option['value'] === $defaultReviewStatus) {
                $option['label'] = $option['label'] . ' (Default)';
                $head[] = $option;
            } else {
                $tail[] = $option;
            }
        }

        $options = array_merge($head, $tail);

        return array_filter($options, function ($option) {
            return $option['value'] !== '';
        });
    }
}
