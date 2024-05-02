<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\QuotePayment\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuotePayment extends AbstractDb
{
    public const TABLE_NAME = 'worldline_quote_payment_information';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
