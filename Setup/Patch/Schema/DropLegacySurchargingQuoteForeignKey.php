<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Drops the foreign key on worldline_surcharging_quote.quote_id ->
 * quote.entity_id regardless of the constraint's actual name in the live
 * database.
 *
 * Same rationale as DropLegacyQuotePaymentInfoForeignKey: older plugin
 * versions may have created this constraint with an auto-generated
 * hash-style name that Magento's declarative schema engine cannot map back
 * to the friendly referenceId declared in db_schema.xml. Without this
 * patch, removing the constraint from db_schema.xml on an existing
 * installation has no effect and sales_clean_quotes continues to fail on
 * quotes with surcharging data.
 */
class DropLegacySurchargingQuoteForeignKey implements SchemaPatchInterface
{
    private const TABLE_NAME = 'worldline_surcharging_quote';
    private const COLUMN_NAME = 'quote_id';
    private const REF_TABLE_NAME = 'quote';
    private const REF_COLUMN_NAME = 'entity_id';

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

        $tableName = $this->moduleDataSetup->getTable(self::TABLE_NAME);
        $refTableName = $this->moduleDataSetup->getTable(self::REF_TABLE_NAME);

        if ($connection->isTableExists($tableName)) {
            foreach ($connection->getForeignKeys($tableName) as $foreignKey) {
                if ($foreignKey['COLUMN_NAME'] === self::COLUMN_NAME
                    && $foreignKey['REF_TABLE_NAME'] === $refTableName
                    && $foreignKey['REF_COLUMN_NAME'] === self::REF_COLUMN_NAME
                ) {
                    $connection->dropForeignKey($tableName, $foreignKey['FK_NAME']);
                }
            }
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
