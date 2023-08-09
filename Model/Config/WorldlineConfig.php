<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Worldline\PaymentCore\Api\Config\WorldlineConfigInterface;

class WorldlineConfig implements WorldlineConfigInterface
{
    public const MODE = 'worldline_connection/connection/environment_mode';
    public const MERCHANT_ID = 'worldline_connection/connection/merchant_id';
    public const API_KEY = 'worldline_connection/connection/api_key';
    public const API_SECRET = 'worldline_connection/connection/api_secret';
    public const API_TEST_ENDPOINT = 'worldline_connection/connection/testing_api_url';
    public const API_PRODUCTION_ENDPOINT = 'worldline_connection/connection/production_api_url';
    public const LOGGING_LIFETIME = 'worldline_debug/general/logging_lifetime';

    /**
     * @var string[]
     */
    private $ccTypesMapper = [
        1 => 'VI',
        2 => 'AE',
        3 => 'MC',
        56 => 'UN',
        117 => 'MI',
        125 => 'JCB',
        130 => 'CB',
        132 => 'DN',
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string | null
     */
    private $apiKey;

    /**
     * @var string | null
     */
    private $apiSecret;

    /**
     * @var string | null
     */
    private $merchantId;

    /**
     * @var string | null
     */
    private $apiEndpoint;

    /**
     * @var bool
     */
    private $isProductionMode;

    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function isProductionMode(?int $scopeCode = null): bool
    {
        if (null === $this->isProductionMode) {
            $this->isProductionMode = $this->scopeConfig->isSetFlag(
                self::MODE,
                ScopeInterface::SCOPE_STORE,
                $scopeCode
            );
        }

        return $this->isProductionMode;
    }

    public function setProductionModeFlag(bool $flag): void
    {
        $this->isProductionMode = $flag;
    }

    public function getMerchantId(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string
    {
        if ($this->merchantId) {
            return $this->merchantId;
        }

        $path = $this->isProductionMode($scopeCode) ? self::MERCHANT_ID . '_prod' : self::MERCHANT_ID;
        return (string) $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function getApiKey(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string
    {
        if ($this->apiKey) {
            return $this->apiKey;
        }

        $path = $this->isProductionMode($scopeCode) ? self::API_KEY . '_prod' : self::API_KEY;
        return (string) $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiSecret(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string
    {
        if ($this->apiSecret) {
            return $this->apiSecret;
        }

        $path = $this->isProductionMode($scopeCode) ? self::API_SECRET . '_prod' : self::API_SECRET;
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue($path, $scopeType, $scopeCode)
        );
    }

    public function setApiSecret(string $apiSecret): void
    {
        $this->apiSecret = $apiSecret;
    }

    public function getApiEndpoint(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string
    {
        if ($this->apiEndpoint) {
            return $this->apiEndpoint;
        }

        $xmlPath = self::API_TEST_ENDPOINT;
        if ($this->isProductionMode($scopeCode)) {
            $xmlPath = self::API_PRODUCTION_ENDPOINT;
        }

        return (string)$this->scopeConfig->getValue($xmlPath, $scopeType, $scopeCode);
    }

    public function setApiEndpoint(string $apiEndpoint): void
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function mapCcType(int $type): ?string
    {
        return $this->ccTypesMapper[$type] ?? null;
    }

    public function getLoggingLifetime(?int $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(self::LOGGING_LIFETIME, ScopeInterface:: SCOPE_STORE, $scopeCode);
    }
}
