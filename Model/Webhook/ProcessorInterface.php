<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;

interface ProcessorInterface
{
    /**
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent);
}
