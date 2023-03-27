<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigDebugProvider
{
    public const LOG_MODE = 'worldline_debug/worldline_request/log_mode';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getLogMode(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(self::LOG_MODE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
