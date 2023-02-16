<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class WebhookConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string[]|null
     */
    private $data;

    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->data = $data;
    }

    public function getKey(?int $storeId = null): string
    {
        return $this->encryptor->decrypt($this->getValue('key', $storeId));
    }

    public function getSecretKey(?int $storeId = null): string
    {
        return $this->encryptor->decrypt($this->getValue('secret_key', $storeId));
    }

    public function getValue(string $configName, ?int $storeId = null): string
    {
        $xmlConfigPath = $this->data[$configName] ?? '';
        if (!$xmlConfigPath) {
            return '';
        }

        return (string) $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
