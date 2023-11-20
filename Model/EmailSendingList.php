<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;
use Worldline\PaymentCore\Model\ResourceModel\EmailSendingList as EmailSendingListResource;

class EmailSendingList extends AbstractModel implements EmailSendingListInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = EmailSendingListResource::TABLE_NAME;

    protected function _construct(): void
    {
        $this->_init(EmailSendingListResource::class);
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    public function getIncrementId(): string
    {
        return $this->_getData(self::INCREMENT_ID);
    }

    public function setIncrementId(string $incrementId): EmailSendingListInterface
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    public function getLevel(): string
    {
        return $this->_getData(self::LEVEL);
    }

    public function setLevel(string $level): EmailSendingListInterface
    {
        $this->setData(self::LEVEL, $level);
        return $this;
    }
}
