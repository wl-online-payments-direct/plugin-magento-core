<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger\ResourceModel\RequestLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog as RequestLogResource;
use Worldline\PaymentCore\Logger\RequestLog as RequestLogModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(RequestLogModel::class, RequestLogResource::class);
    }

    public function getIdFieldName(): string
    {
        return 'id';
    }
}
