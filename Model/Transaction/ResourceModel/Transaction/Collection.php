<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;
use Worldline\PaymentCore\Model\Transaction\Transaction as TransactionModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(TransactionModel::class, TransactionResource::class);
    }

    protected function _afterLoad(): Collection
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }

        return parent::_afterLoad();
    }
}
