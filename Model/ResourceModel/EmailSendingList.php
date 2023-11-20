<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;

class EmailSendingList extends AbstractDb
{
    public const TABLE_NAME = 'worldline_email_sending_list';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, EmailSendingListInterface::ENTITY_ID);
    }
}
