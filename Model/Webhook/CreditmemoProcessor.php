<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;
use Worldline\PaymentCore\Model\RefundRequest\RefundProcessor;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

class CreditmemoProcessor implements ProcessorInterface
{
    /**
     * @var RefundProcessor
     */
    private $refundProcessor;

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
        RefundRequestRepositoryInterface $refundRequestRepository,
        TransactionWLResponseManagerInterface $transactionWLResponseManager
    ) {
        $this->refundProcessor = $refundProcessor;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
    }

    public function process(WebhooksEvent $webhookEvent): void
    {
        /** @var RefundResponse $refundResponse */
        $refundResponse = $webhookEvent->getRefund();
        $statusCode = (int)$refundResponse->getStatusOutput()->getStatusCode();
        if ($statusCode === TransactionStatusInterface::REFUND_UNCERTAIN_CODE) {
            return;
        }

        sleep(5);

        if ($statusCode === TransactionStatusInterface::REFUNDED_CODE) {
            $incrementId = $refundResponse->getRefundOutput()->getReferences()->getMerchantReference();
            $amount = (int)$refundResponse->getRefundOutput()->getAmountOfMoney()->getAmount();
            $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount((string)$incrementId, $amount);

            if (!$refundRequest->getCreditMemoId()) {
                $this->handleSplitPayment($incrementId, $amount, $refundRequest);
            }

            $this->transactionWLResponseManager->saveTransaction($refundResponse);

            $this->refundProcessor->process($refundRequest);
        }
    }

    /**
     * @param $incrementId
     * @param $amount
     * @param $refundRequest
     *
     * @return void
     */
    private function handleSplitPayment($incrementId, $amount, &$refundRequest): void
    {
        $refunds = $this->refundRequestRepository->getListByIncrementId($incrementId);

        if (empty($refunds)) {
            return;
        }
        $refund = reset($refunds);

        $leftToRefund = $refund->getAmount() - $amount;

        // handle partial payment
        if ($leftToRefund < 0) {
            $creditMemo = $this->refundProcessor->getCreditMemoById($refund->getCreditMemoId());
            $invoice = $creditMemo->getInvoice();
            $leftToRefund = ($invoice->getBaseGrandTotal() - $creditMemo->getGrandTotal()) * 100;
        }

        $refund->setAmount((int) $leftToRefund);
        $this->refundRequestRepository->save($refund);

        if ($leftToRefund !== 0) {
            return;
        }

        $refundRequest->setCreditMemoId($refund->getCreditMemoId());
    }
}