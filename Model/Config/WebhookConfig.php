<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class WebhookConfig
{
    public const KEY = 'worldline_connection/webhook/key';
    public const SECRET_KEY = 'worldline_connection/webhook/secret_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function getKey(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSecretKey(?int $storeId = null): string
    {
        return $this->encryptor->decrypt(
            (string) $this->scopeConfig->getValue(self::SECRET_KEY, ScopeInterface::SCOPE_STORE, $storeId)
        );
    }
}
