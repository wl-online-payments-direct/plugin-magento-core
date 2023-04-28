<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

/**
 * Handle the payment.cancelled type webhook
 */
class CancelledProcessor implements ProcessorInterface
{
    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    public function __construct(
        TransactionWLResponseManagerInterface $transactionWLResponseManager
    ) {
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
        /** @var PaymentResponse $response */
        $response = $webhookEvent->getPayment();
        $statusCode = (int)$response->getStatusOutput()->getStatusCode();
        if ($statusCode !== TransactionStatusInterface::AUTHORISED_AND_CANCELLED) {
            return;
        }

        $this->transactionWLResponseManager->saveTransaction($response);
    }
}
