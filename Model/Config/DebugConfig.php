<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class DebugConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]|null
     */
    private $data;

    public function __construct(ScopeConfigInterface $scopeConfig, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
    }

    public function isWebhookLogEnabled(): bool
    {
        $xmlConfigPath = $this->data['webhook_log_active'] ?? '';
        if (!$xmlConfigPath) {
            return false;
        }

        return $this->scopeConfig->isSetFlag($xmlConfigPath);
    }
}
