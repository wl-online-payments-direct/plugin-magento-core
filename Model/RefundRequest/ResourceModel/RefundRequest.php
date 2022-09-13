<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\PaymentCore\Api\Data\RefundRequestInterface;

class RefundRequest extends AbstractDb
{
    public const TABLE_NAME = 'worldline_payment_refund_request';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, RefundRequestInterface::ENTITY_ID);
    }
}
