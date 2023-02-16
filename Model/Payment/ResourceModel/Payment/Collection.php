<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\Payment\Payment as PaymentModel;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment as PaymentResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(PaymentModel::class, PaymentResource::class);
    }
}
