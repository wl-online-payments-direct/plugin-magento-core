<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\PaymentCore\Api\Data\RequestLogInterface;

class RequestLog extends AbstractDb
{
    public const TABLE_NAME = 'worldline_request_log';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    public function hasErrorRequests(): bool
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable())
            ->where(RequestLogInterface::MARK_AS_PROCESSED . ' = ?', 0)
            ->where(RequestLogInterface::RESPONSE_CODE . ' >= ?', 400);
        return (bool) $this->getConnection()->fetchOne($select);
    }

    public function clearRecordsByDate(string $date): RequestLog
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [RequestLogInterface::CREATED_AT . ' <= ?' => $date]
        );

        return $this;
    }

    public function changeStatus(array $ids, int $status): RequestLog
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            [RequestLogInterface::MARK_AS_PROCESSED => $status],
            ['id IN (?)' => $ids]
        );

        return $this;
    }
}
