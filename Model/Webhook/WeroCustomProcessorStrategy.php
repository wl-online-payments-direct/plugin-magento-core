<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\Webhook\CustomProcessorStrategyInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

class WeroCustomProcessorStrategy implements CustomProcessorStrategyInterface
{
    private $weroOrderProcessor;

    public function __construct(WeroOrderProcessor $weroOrderProcessor)
    {
        $this->weroOrderProcessor = $weroOrderProcessor;
    }

    public function getProcessor(WebhooksEvent $webhookEvent): ?ProcessorInterface
    {
        if (!$payment = $webhookEvent->getPayment()) {
            return null;
        }

        $redirectOutput = $payment->getPaymentOutput()->getRedirectPaymentMethodSpecificOutput();
        $statusCode = $payment->getStatusOutput()->getStatusCode();
        if (!$redirectOutput
            || $statusCode !== TransactionStatusInterface::CAPTURED_CODE
            || PaymentProductsDetailsInterface::WERO_PRODUCT_ID !== $redirectOutput->getPaymentProductId()
        ) {
            return null;
        }

        return $this->weroOrderProcessor;
    }
}
