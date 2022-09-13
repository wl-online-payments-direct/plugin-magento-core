<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\WebhooksEvent;

interface TransactionWebhookManagerInterface
{
    public function saveTransaction(WebhooksEvent $webhookEvent): void;
}
