<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingCreditmemo;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\SurchargingCreditmemoInterface;
use Worldline\PaymentCore\Model\SurchargingCreditmemo\ResourceModel;

class SurchargingCreditmemo extends AbstractModel implements SurchargingCreditmemoInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'worldline_surcharging_creditmemo';

    protected function _construct(): void
    {
        $this->_init(ResourceModel\SurchargingCreditmemo::class);
    }
}
