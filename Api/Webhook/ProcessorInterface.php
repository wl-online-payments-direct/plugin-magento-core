<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;

interface ProcessorInterface
{
    /**
     * Process webhook content
     *
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent): void;
}
