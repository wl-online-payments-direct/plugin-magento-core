<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Model\Order\PaymentInfoUpdater;

/**
 * Update credit memo when webhooks are missing
 */
class CreditmemoUpdater
{
    /**
     * @var PaymentInfoUpdater
     */
    private $paymentInfoUpdater;

    /**
     * @var RefundProcessor
     */
    private $refundProcessor;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var RefundRefusedProcessor
     */
    private $refundRefusedProcessor;

    /**
     * @var RefundRefusedNotification
     */
    private $refundRefusedNotification;

    public function __construct(
        PaymentInfoUpdater $paymentInfoUpdater,
        RefundProcessor $refundProcessor,
        RefundRequestRepositoryInterface $refundRequestRepository,
        TransactionRepositoryInterface $transactionRepository,
        QuoteResourceInterface $quoteResource,
        AmountFormatterInterface $amountFormatter,
        RefundRefusedProcessor $refundRefusedProcessor,
        RefundRefusedNotification $refundRefusedNotification
    ) {
        $this->paymentInfoUpdater = $paymentInfoUpdater;
        $this->refundProcessor = $refundProcessor;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->transactionRepository = $transactionRepository;
        $this->quoteResource = $quoteResource;
        $this->amountFormatter = $amountFormatter;
        $this->refundRefusedProcessor = $refundRefusedProcessor;
        $this->refundRefusedNotification = $refundRefusedNotification;
    }

    public function update(string $incrementId, string $grandTotal, int $storeId): void
    {
        $updateResult = $this->paymentInfoUpdater->updateForIncrementId($incrementId, $storeId);
        if (!$updateResult) {
            return;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote) {
            return;
        }

        $currentAmount = $this->amountFormatter->formatToInteger(
            (float) $grandTotal,
            (string) $quote->getCurrency()->getQuoteCurrencyCode()
        );

        $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount($incrementId, $currentAmount);
        $creditmemoId = $refundRequest->getCreditMemoId();
        if (!$creditmemoId) {
            return;
        }

        if (!$this->isExistRefundedTransaction($incrementId, $currentAmount)) {
            if ($this->isRefundRejected($incrementId, $currentAmount)) {
                $this->refundRefusedProcessor->process($refundRequest);
                $this->refundRefusedNotification->notify($quote, $incrementId, $creditmemoId);
            }
            return;
        }

        $this->refundProcessor->process($refundRequest);
    }

    private function isExistRefundedTransaction(string $incrementId, int $currentAmount): bool
    {
        $refundedTransactions = $this->transactionRepository->getRefundedTransactions($incrementId);
        /** @var TransactionInterface $transaction */
        foreach ($refundedTransactions as $transaction) {
            $refundedAmount = (int) round($transaction->getAmount());
            if ($currentAmount === $refundedAmount) {
                return true;
            }
        }

        return false;
    }

    private function isRefundRejected(string $incrementId, int $currentAmount): bool
    {
        $rejectedTransactions = $this->transactionRepository->getRefundRejectedTransactions($incrementId);
        /** @var TransactionInterface $transaction */
        foreach ($rejectedTransactions as $transaction) {
            $refundedAmount = (int) round($transaction->getAmount());
            if ($currentAmount === $refundedAmount) {
                return true;
            }
        }

        return false;
    }
}
