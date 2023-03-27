<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Config;

use Magento\Store\Model\ScopeInterface;

interface WorldlineConfigInterface
{
    public function isProductionMode(?int $scopeCode = null): bool;

    public function setProductionModeFlag(bool $flag): void;

    public function getMerchantId(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string;

    public function setMerchantId(string $merchantId): void;

    public function getApiKey(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string;

    public function setApiKey(string $apiKey): void;

    public function getApiSecret(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string;

    public function setApiSecret(string $apiSecret): void;

    public function getApiEndpoint(?int $scopeCode = null, ?string $scopeType = ScopeInterface::SCOPE_STORE): string;

    public function setApiEndpoint(string $apiEndpoint): void;

    public function mapCcType(int $type): ?string;

    public function getLoggingLifetime(?int $scopeCode = null): ?string;
}
