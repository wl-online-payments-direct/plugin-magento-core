<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Model\Config\GeneralSettingsConfig;

class SetAuthExemptionDefaults implements DataPatchInterface
{
    const LOW_VALUE_EXEMPTION_TYPE = 'low-value';
    const LOW_VALUE_EXEMPTION_AMOUNT = '30';
    const MAGENTO_CONFIG_TABLE_NAME = 'core_config_data';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        ResourceConnection $resource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->resource = $resource;
    }

    public function apply(): self
    {
        $connection = $this->resource->getConnection();
        $configTable = $this->resource->getTableName(self::MAGENTO_CONFIG_TABLE_NAME);
        $path = GeneralSettingsConfig::AUTH_EXEMPTION;

        // Save values only for websites where auth_exemption is explicitly set
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = (int)$website->getId();

            $select = $connection->select()
                ->from($configTable)
                ->where('scope = ?', ScopeInterface::SCOPE_WEBSITES)
                ->where('scope_id = ?', $websiteId)
                ->where('path = ?', $path);

            $explicitConfig = $connection->fetchRow($select);

            if ($explicitConfig && (int)$explicitConfig['value'] === 1) {
                $this->configWriter->save(
                    GeneralSettingsConfig::AUTH_EXEMPTION_TYPE,
                    self::LOW_VALUE_EXEMPTION_TYPE,
                    ScopeInterface::SCOPE_WEBSITES,
                    $websiteId
                );
                $this->configWriter->save(
                    GeneralSettingsConfig::AUTH_LOW_VALUE_AMOUNT,
                    self::LOW_VALUE_EXEMPTION_AMOUNT,
                    ScopeInterface::SCOPE_WEBSITES,
                    $websiteId
                );
            }
        }

        // Apply to default scope if enabled
        if ($this->scopeConfig->getValue(
            GeneralSettingsConfig::AUTH_EXEMPTION,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        )) {
            $this->configWriter->save(
                GeneralSettingsConfig::AUTH_EXEMPTION_TYPE,
                self::LOW_VALUE_EXEMPTION_TYPE,
                'default',
                0
            );
            $this->configWriter->save(
                GeneralSettingsConfig::AUTH_LOW_VALUE_AMOUNT,
                self::LOW_VALUE_EXEMPTION_AMOUNT,
                'default',
                0
            );
        }

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
