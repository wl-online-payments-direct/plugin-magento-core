<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\PaymentCore\Api\Data\WebhookInterface;

/**
 * Resource model for webhook entity
 */
class Webhook extends AbstractDb
{
    public const TABLE_NAME = 'worldline_webhook';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * Truncate table
     *
     * @return void
     * @throws LocalizedException
     */
    public function clearTable(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    /**
     * Clear records
     *
     * @param string $date
     * @return void
     * @throws LocalizedException
     */
    public function clearRecordsByDate(string $date): void
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [WebhookInterface::CREATED_AT . ' <= ?' => $date]
        );
    }
}
