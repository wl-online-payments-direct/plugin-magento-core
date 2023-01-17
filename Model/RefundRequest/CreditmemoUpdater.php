<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionUpdater;

/**
 * Update credit memo when webhooks are missing
 */
class CreditmemoUpdater
{
    /**
     * @var TransactionUpdater
     */
    private $transactionUpdater;

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

    public function __construct(
        TransactionUpdater $transactionUpdater,
        RefundProcessor $refundProcessor,
        RefundRequestRepositoryInterface $refundRequestRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionUpdater = $transactionUpdater;
        $this->refundProcessor = $refundProcessor;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function update(string $incrementId, string $grandTotal, int $storeId): void
    {
        $updateResult = $this->transactionUpdater->updateForIncrementId($incrementId, $storeId);
        if (!$updateResult) {
            return;
        }

        $currentAmount = (int) round($grandTotal * 100);
        if (!$this->isExistCreditmemo($incrementId, $currentAmount)) {
            return;
        }

        $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount($incrementId, $currentAmount);
        if (!$refundRequest->getCreditMemoId()) {
            return;
        }

        $this->refundProcessor->process($refundRequest);
    }

    private function isExistCreditmemo(string $incrementId, int $currentAmount): bool
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
}
