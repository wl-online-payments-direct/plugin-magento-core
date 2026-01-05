<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\Model\WebhookConfig;

class SetDefaultWebhookMode implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        if ($this->configExists(WebhookConfig::WEBHOOK_MODE)) {
            $this->moduleDataSetup->endSetup();
            return;
        }

        if ($this->configExists(WorldlineConfig::MERCHANT_ID)) {
            $this->configWriter->save(WebhookConfig::WEBHOOK_MODE, 0);
        }

        $this->moduleDataSetup->endSetup();
    }

    private function configExists(string $path): bool
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('core_config_data');

        $select = $connection->select()
            ->from($tableName, 'config_id')
            ->where('path = ?', $path)
            ->limit(1);

        return (bool) $connection->fetchOne($select);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
