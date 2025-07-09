<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\WebhooksEvent;

/**
 * Extract response for different types of webhook events
 */
class WebhookResponseManager
{
    /**
     * @param WebhooksEvent $webhookEvent
     * @return DataObject (PaymentResponse|RefundResponse)
     * @throws LocalizedException
     */
    public function getResponse(WebhooksEvent $webhookEvent): DataObject
    {
        $response = null;
        if ($webhookEvent->getPayment()) {
            $response = $webhookEvent->getPayment();
        } elseif ($webhookEvent->getRefund()) {
            $response = $webhookEvent->getRefund();
        }

        if (!$response) {
            throw new LocalizedException(__('Invalid response model'));
        }

        return $response;
    }
}
