<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class DebugConfig
{
    public const WEBHOOK_LOG_ACTIVE = 'worldline_debug/webhooks/active';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isWebhookLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::WEBHOOK_LOG_ACTIVE);
    }
}
