<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class DecryptPublicKeys implements DataPatchInterface
{
    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        Manager $cacheManager,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger,
        EncryptorInterface $encryptor
    ) {
        $this->cacheManager = $cacheManager;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
    }

    public function apply(): DecryptPublicKeys
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        if (!$configRows = $this->getRowsToDecrypt()) {
            $connection->endSetup();
            return $this;
        }

        $this->updateConfigRows($configRows);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

        $connection->endSetup();
        return $this;
    }

    private function getRowsToDecrypt(): array
    {
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'))
            ->where('path IN (
                    "worldline_connection/connection/api_key",
                    "worldline_connection/connection/api_key_prod",
                    "worldline_connection/webhook/key")');

        return $this->moduleDataSetup->getConnection()->fetchAll($select);
    }

    private function updateConfigRows(array $configRows): void
    {
        foreach ($configRows as $configRow) {
            try {
                $this->moduleDataSetup->getConnection()
                    ->update(
                        $this->moduleDataSetup->getTable('core_config_data'),
                        ['value' => $this->encryptor->decrypt($configRow['value'])],
                        ['config_id = ?' => $configRow['config_id']]
                    );
            } catch (\Exception $exception) {
                $this->logger->warning($exception->getMessage());
            }
        }
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
