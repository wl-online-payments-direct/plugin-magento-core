<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    public const TABLE_NAME = 'worldline_payment_transaction';

    /**
     * @var array
     */
    protected $_serializableFields = ['additional_data' => [null, []]];

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    public function removeByIncrementId(string $incrementId): void
    {
        $this->getConnection()->delete($this->getMainTable(), ['increment_id = ?' => $incrementId]);
    }

    public function insertMultipleTransactions(array $transactions): void
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $transactions);
    }
}
