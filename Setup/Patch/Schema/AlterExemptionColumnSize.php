<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class AlterExemptionColumnSize implements SchemaPatchInterface
{
    const FRAUD_INFORMATION_TABLE_NAME = 'worldline_fraud_information';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $tableName = $this->moduleDataSetup->getTable(self::FRAUD_INFORMATION_TABLE_NAME);

        if ($connection->isTableExists($tableName)) {
            $connection->changeColumn(
                $tableName,
                'exemption',
                'exemption',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Exemption'
                ]
            );
        }
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
