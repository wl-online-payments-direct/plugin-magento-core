<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingQuote\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SurchargingQuote extends AbstractDb
{
    public const TABLE_NAME = 'worldline_surcharging_quote';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
