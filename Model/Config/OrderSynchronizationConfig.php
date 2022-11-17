<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class OrderSynchronizationConfig
{
    public const REFUSED_PAYMENT_SENDER_PATH = 'worldline_order_creator/general/refused_payment_sender';
    public const REFUSED_PAYMENT_TEMPLATE_PATH = 'worldline_order_creator/general/refused_payment_template';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]|null
     */
    private $data;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string[]|null $data
     */
    public function __construct(ScopeConfigInterface $scopeConfig, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
    }

    public function getFallbackTimeout(?int $storeId = null): int
    {
        return (int) $this->getValue('fallback_timeout', $storeId);
    }

    public function getFallbackTimeoutLimit(?int $storeId = null): int
    {
        return (int) $this->getValue('fallback_timeout_limit', $storeId);
    }

    public function getRefusedPaymentTemplate(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(self::REFUSED_PAYMENT_TEMPLATE_PATH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRefusedPaymentSender(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(self::REFUSED_PAYMENT_SENDER_PATH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function getValue(string $configName, ?int $storeId = null): string
    {
        $xmlConfigPath = $this->data[$configName] ?? '';
        if (!$xmlConfigPath) {
            return '';
        }

        return (string) $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
