<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel\EmailSendingList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\EmailSendingList as EmailSenderModel;
use Worldline\PaymentCore\Model\ResourceModel\EmailSendingList as EmailSenderResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(EmailSenderModel::class, EmailSenderResource::class);
    }

    public function getIdFieldName(): string
    {
        return 'entity_id';
    }
}
