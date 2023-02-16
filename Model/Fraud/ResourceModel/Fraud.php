<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Fraud\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for fraud entity
 */
class Fraud extends AbstractDb
{
    public const TABLE_NAME = 'worldline_fraud_information';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
