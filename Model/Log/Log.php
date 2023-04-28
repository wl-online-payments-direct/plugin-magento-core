<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Log;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\LogInterface;
use Worldline\PaymentCore\Model\Log\ResourceModel\Log as LogResource;

class Log extends AbstractModel implements LogInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = LogResource::TABLE_NAME;

    protected function _construct(): void
    {
        $this->_init(LogResource::class);
    }

    public function getLogId(): int
    {
        return $this->_getData(self::LOG_ID);
    }

    public function getContent(): string
    {
        return $this->_getData(self::CONTENT);
    }

    public function setContent(string $content): void
    {
        $this->setData(self::CONTENT, $content);
    }

    public function getCreatedAt(): string
    {
        return $this->_getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $dateTime): void
    {
        $this->setData(self::CREATED_AT, $dateTime);
    }
}
