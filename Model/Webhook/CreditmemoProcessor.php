<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\RefundRequest\RefundProcessor;

class CreditmemoProcessor implements ProcessorInterface
{
    public const REFUND_CODE = 8;
    public const REFUND_UNCERTAIN_CODE = 82;

    /**
     * @var RefundProcessor
     */
    private $refundProcessor;

    /**
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    public function __construct(
        RefundProcessor $refundProcessor,
        WebhookResponseManager $webhookResponseManager,
        RefundRequestRepositoryInterface $refundRequestRepository,
        TransactionWLResponseManagerInterface $transactionWLResponseManager
    ) {
        $this->refundProcessor = $refundProcessor;
        $this->webhookResponseManager = $webhookResponseManager;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        /** @var RefundResponse $refundResponse */
        $refundResponse = $this->webhookResponseManager->getResponse($webhookEvent);
        $statusCode = (int)$refundResponse->getStatusOutput()->getStatusCode();
        if ($statusCode === self::REFUND_UNCERTAIN_CODE) {
            return;
        }

        if ($statusCode === self::REFUND_CODE) {
            $incrementId = $refundResponse->getRefundOutput()->getReferences()->getMerchantReference();
            $amount = (int)$refundResponse->getRefundOutput()->getAmountOfMoney()->getAmount();
            $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount((string)$incrementId, $amount);
            if (!$refundRequest->getCreditMemoId()) {
                return;
            }

            $this->transactionWLResponseManager->saveTransaction($refundResponse);

            $this->refundProcessor->process($refundRequest);
        }
    }
}
