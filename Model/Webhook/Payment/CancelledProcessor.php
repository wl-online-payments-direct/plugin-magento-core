<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;
use Worldline\PaymentCore\Model\Webhook\ProcessorInterface;
use Worldline\PaymentCore\Model\Webhook\WebhookResponseManager;

/**
 * Handle the payment.cancelled type webhook
 */
class CancelledProcessor implements ProcessorInterface
{
    /**
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    public function __construct(
        WebhookResponseManager $webhookResponseManager,
        TransactionWLResponseManagerInterface $transactionWLResponseManager
    ) {
        $this->webhookResponseManager = $webhookResponseManager;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
    }

    /**
     * Process the payment.cancelled type webhook
     *
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent): void
    {
        $response = $this->webhookResponseManager->getResponse($webhookEvent);
        $statusCode = (int)$response->getStatusOutput()->getStatusCode();
        if ($statusCode !== TransactionStatusInterface::AUTHORISED_AND_CANCELLED) {
            return;
        }

        $this->transactionWLResponseManager->saveTransaction($response);
    }
}
