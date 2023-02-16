<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Infrastructure\Plugin\WebhookConfig;

use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;
use Worldline\PaymentCore\Model\Config\WebhookConfig;

class DecryptedKey
{
    /**
     * @var ServiceStubSwitcherInterface
     */
    private $serviceStubSwitcher;

    public function __construct(ServiceStubSwitcherInterface $serviceStubSwitcher)
    {
        $this->serviceStubSwitcher = $serviceStubSwitcher;
    }

    public function aroundGetKey(WebhookConfig $subject, callable $proceed, ?int $storeId = null): string
    {
        if ($this->serviceStubSwitcher->isEnabled()) {
            return $subject->getValue('key', $storeId);
        }

        return $proceed($storeId);
    }

    public function aroundGetSecretKey(WebhookConfig $subject, callable $proceed, ?int $storeId = null): string
    {
        if ($this->serviceStubSwitcher->isEnabled()) {
            return $subject->getValue('secret_key', $storeId);
        }

        return $proceed($storeId);
    }
}
