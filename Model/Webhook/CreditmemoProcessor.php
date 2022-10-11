<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Data\RefundRequestInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\RefundRequest\CreditmemoOfflineService;
use Worldline\PaymentCore\Model\RefundRequest\EmailNotification;

class CreditmemoProcessor implements ProcessorInterface
{
    public const REFUND_CODE = 8;
    public const REFUND_UNCERTAIN_CODE = 82;

    /**
     * @var CreditmemoOfflineService
     */
    private $refundOfflineService;

    /**
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var EmailNotification
     */
    private $emailNotification;

    public function __construct(
        CreditmemoOfflineService $refundOfflineService,
        WebhookResponseManager $webhookResponseManager,
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        EmailNotification $emailNotification
    ) {
        $this->refundOfflineService = $refundOfflineService;
        $this->webhookResponseManager = $webhookResponseManager;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->emailNotification = $emailNotification;
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

            $this->processRefund($refundRequest);
        }
    }

    private function processRefund(RefundRequestInterface $refundRequest): void
    {
        $creditmemoEntity = $this->creditmemoRepository->get($refundRequest->getCreditMemoId());

        $this->refundOfflineService->refund($creditmemoEntity);

        $refundRequest->setRefunded(true);
        $this->refundRequestRepository->save($refundRequest);

        $this->emailNotification->send($creditmemoEntity);
    }
}
