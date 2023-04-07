<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure;

use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;

class ServiceStubSwitcher implements ServiceStubSwitcherInterface
{
    /**
     * @var bool
     */
    private $enabled = false;

    public function setEnabled(bool $enabled): ServiceStubSwitcherInterface
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
