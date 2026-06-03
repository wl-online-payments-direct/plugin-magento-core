<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Drops the foreign key on worldline_quote_payment_information.payment_id ->
 * quote_payment.payment_id regardless of the constraint's actual name in the
 * live database.
 *
 * Older Worldline plugin versions created this constraint with auto-generated
 * hash-style names (e.g. FK_69BEB02400D33E1B179FF6E997B80211). Magento's
 * declarative schema engine looks up constraints by the referenceId declared
 * in db_schema.xml and does not map between friendly and hash-style names,
 * so removing the constraint from db_schema.xml alone is not enough on
 * existing installations — the FK is silently left in place and the
 * sales_clean_quotes cron continues to fail.
 *
 * This patch resolves the constraint via the connection's getForeignKeys()
 * introspection and drops it explicitly, which works regardless of the
 * constraint name.
 */
class DropLegacyQuotePaymentInfoForeignKey implements SchemaPatchInterface
{
    private const TABLE_NAME = 'worldline_quote_payment_information';
    private const COLUMN_NAME = 'payment_id';
    private const REF_TABLE_NAME = 'quote_payment';
    private const REF_COLUMN_NAME = 'payment_id';

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
