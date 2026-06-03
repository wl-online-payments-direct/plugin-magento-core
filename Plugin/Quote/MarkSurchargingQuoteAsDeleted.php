<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Psr\Log\LoggerInterface;

/**
 * Marks Worldline surcharging quote rows as soft-deleted when the related
 * Magento quote is deleted.
 */
class MarkSurchargingQuoteAsDeleted
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function beforeDelete(QuoteResource $subject, Quote $quote): void
    {
        unset($subject);
        
        try {
            $connection = $this->resourceConnection->getConnection();

            $connection->update(
                $this->resourceConnection->getTableName('worldline_surcharging_quote'),
                ['deleted_at' => new \Zend_Db_Expr('NOW()')],
                [
                    'quote_id = ?' => $quote->getId(),
                    'deleted_at IS NULL',
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Worldline: failed to mark surcharging quote as deleted: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
