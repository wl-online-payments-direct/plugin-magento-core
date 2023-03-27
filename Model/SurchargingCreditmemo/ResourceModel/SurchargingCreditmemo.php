<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingCreditmemo\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SurchargingCreditmemo extends AbstractDb
{
    public const TABLE_NAME = 'worldline_surcharging_creditmemo';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
