<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest\ResourceModel\RefundRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\RefundRequest\RefundRequest as RefundRequestModel;
use Worldline\PaymentCore\Model\RefundRequest\ResourceModel\RefundRequest as RefundRequestResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(RefundRequestModel::class, RefundRequestResource::class);
    }

    public function getIdFieldName(): string
    {
        return 'id';
    }
}
