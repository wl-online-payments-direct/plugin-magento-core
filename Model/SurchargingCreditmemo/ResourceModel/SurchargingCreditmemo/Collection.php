<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingCreditmemo\ResourceModel\SurchargingCreditmemo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\SurchargingCreditmemo\SurchargingCreditmemo;
use Worldline\PaymentCore\Model\SurchargingCreditmemo\ResourceModel\SurchargingCreditmemo as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(SurchargingCreditmemo::class, ResourceModel::class);
    }
}
