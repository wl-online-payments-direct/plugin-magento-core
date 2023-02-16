<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction\Collection as TransactionCollection;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;

class UpdateTransactionTable implements DataPatchInterface
{
    /**
     * @var TransactionCollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TransactionCollectionFactory $transactionCollectionFactory,
        LoggerInterface $logger,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->amountFormatter = $amountFormatter;
    }

    public function apply(): UpdateTransactionTable
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        /** @var TransactionCollection $collection */
        $collection = $this->transactionCollectionFactory->create();
        $table = $this->moduleDataSetup->getTable(TransactionResource::TABLE_NAME);

        /** @var TransactionInterface $item */
        foreach ($collection->getItems() as $item) {
            $amount = $this->amountFormatter->formatToInteger(
                (float) $item->getAmount(),
                (string) $item->getCurrency()
            );
            try {
                $connection->update($table, ['amount' => $amount], ['entity_id = ?' => $item->getId()]);
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage(), $e->getTrace());
            }
        }

        $connection->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [
            ClearDuplicatesTransactionTable::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
