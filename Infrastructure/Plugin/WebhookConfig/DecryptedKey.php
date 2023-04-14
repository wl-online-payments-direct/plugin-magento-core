<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\Plugin\WebhookConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;
use Worldline\PaymentCore\Model\Config\WebhookConfig;

class DecryptedKey
{
    /**
     * @var ServiceStubSwitcherInterface
     */
    private $serviceStubSwitcher;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ServiceStubSwitcherInterface $serviceStubSwitcher, ScopeConfigInterface $scopeConfig)
    {
        $this->serviceStubSwitcher = $serviceStubSwitcher;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param WebhookConfig $subject
     * @param callable $proceed
     * @param int|null $storeId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetSecretKey(WebhookConfig $subject, callable $proceed, ?int $storeId = null): string
    {
        if ($this->serviceStubSwitcher->isEnabled()) {
            return (string) $this->scopeConfig->getValue(
                WebhookConfig::SECRET_KEY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $proceed($storeId);
    }
}
