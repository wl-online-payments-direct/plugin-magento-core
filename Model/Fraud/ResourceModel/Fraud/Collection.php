<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Fraud\ResourceModel\Fraud;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\Fraud\Fraud as FraudModel;
use Worldline\PaymentCore\Model\Fraud\ResourceModel\Fraud as FraudResource;

/**
 * Collection for fraud entity
 */
class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(FraudModel::class, FraudResource::class);
    }
}
