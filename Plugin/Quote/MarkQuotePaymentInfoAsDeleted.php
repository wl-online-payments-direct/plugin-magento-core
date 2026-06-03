<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Psr\Log\LoggerInterface;

/**
 * Marks Worldline quote payment information rows as soft-deleted when the related
 * Magento quote is deleted.
 */
class MarkQuotePaymentInfoAsDeleted
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

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('quote_payment'), ['payment_id'])
                ->where('quote_id = ?', $quote->getId());

            $paymentIds = $connection->fetchCol($select);

            if (empty($paymentIds)) {
                return;
            }

            $connection->update(
                $this->resourceConnection->getTableName('worldline_quote_payment_information'),
                ['deleted_at' => new \Zend_Db_Expr('NOW()')],
                [
                    'payment_id IN (?)' => $paymentIds,
                    'deleted_at IS NULL',
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Worldline: failed to mark quote payment information as deleted: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
