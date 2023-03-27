<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Webhook;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\WebhooksEvent;

/**
 * Helper for a place order processor
 */
interface PlaceOrderManagerInterface
{
    public function getValidatedQuote(WebhooksEvent $webhookEvent): ?CartInterface;
}
