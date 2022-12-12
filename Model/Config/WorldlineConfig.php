<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class WorldlineConfig
{
    public const XML_PATH_MODE = 'worldline_connection/connection/environment_mode';
    public const XML_PATH_MERCHANT_ID = 'worldline_connection/connection/merchant_id';
    public const XML_PATH_API_KEY = 'worldline_connection/connection/api_key';
    public const XML_PATH_API_SECRET = 'worldline_connection/connection/api_secret';
    public const XML_PATH_API_TEST_ENDPOINT = 'worldline_connection/connection/testing_api_url';
    public const XML_PATH_API_PRODUCTION_ENDPOINT = 'worldline_connection/connection/production_api_url';
    public const XML_PATH_LOGGING_LIFETIME = 'worldline_debug/general/logging_lifetime';

    /**
     * @var string[]
     */
    private $ccTypesMapper = [
        1 => 'VI',
        2 => 'AE',
        3 => 'MC',
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
    private $apiKey = null;

    /**
     * @var string | null
     */
    private $apiSecret = null;

    /**
     * @var string | null
     */
    private $merchantId = null;

    /**
     * @var string | null
     */
    private $apiEndpoint = null;

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
                self::XML_PATH_MODE,
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

        $path = $this->isProductionMode($scopeCode) ? self::XML_PATH_MERCHANT_ID . '_prod' : self::XML_PATH_MERCHANT_ID;
        $this->merchantId = (string)$this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        return $this->merchantId;
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

        $path = $this->isProductionMode($scopeCode) ? self::XML_PATH_API_KEY . '_prod' : self::XML_PATH_API_KEY;
        $this->apiKey = $this->encryptor->decrypt(
            $this->scopeConfig->getValue($path, $scopeType, $scopeCode)
        );
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiSecret(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE):string
    {
        if ($this->apiSecret) {
            return $this->apiSecret;
        }

        $path = $this->isProductionMode($scopeCode) ? self::XML_PATH_API_SECRET . '_prod' : self::XML_PATH_API_SECRET;
        $this->apiSecret = $this->encryptor->decrypt(
            $this->scopeConfig->getValue($path, $scopeType, $scopeCode)
        );
        return $this->apiSecret;
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

        $xmlPath = self::XML_PATH_API_TEST_ENDPOINT;
        if ($this->isProductionMode($scopeCode)) {
            $xmlPath = self::XML_PATH_API_PRODUCTION_ENDPOINT;
        }

        $this->apiEndpoint = (string)$this->scopeConfig->getValue($xmlPath, $scopeType, $scopeCode);

        return $this->apiEndpoint;
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
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGGING_LIFETIME,
            ScopeInterface:: SCOPE_STORE,
            $scopeCode
        );
    }
}
