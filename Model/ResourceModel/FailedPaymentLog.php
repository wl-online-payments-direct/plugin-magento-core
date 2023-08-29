<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class FailedPaymentLog
{
    public const TABLE = 'worldline_failed_payment_log';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function saveQuotePaymentId(int $quotePaymentId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $this->resourceConnection->getTableName(self::TABLE),
            ['quote_payment_id' => $quotePaymentId]
        );
    }
}
