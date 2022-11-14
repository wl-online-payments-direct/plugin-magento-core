<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction;

class ClearDuplicatesTransactionTable implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): ClearDuplicatesTransactionTable
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $select = $connection->select()->from(
            ['worldline_pt' => $this->moduleDataSetup->getTable(Transaction::TABLE_NAME)]
        )->joinLeft(
            ['sales_pt' => $this->moduleDataSetup->getTable('sales_payment_transaction')],
            'sales_pt.txn_id = worldline_pt.transaction_id'
        )->where('sales_pt.txn_id IS NULL');

        $connection->query($connection->deleteFromSelect($select, 'worldline_pt'));

        $connection->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
