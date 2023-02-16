<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Test\Infrastructure;

use Magento\Framework\Controller\ResultInterface;

interface WebhookStubSenderInterface
{
    public function sendWebhook(string $content, array $headers = []): ResultInterface;
}
