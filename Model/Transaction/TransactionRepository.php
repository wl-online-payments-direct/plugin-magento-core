<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction\CollectionFactory;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var array
     */
    private $transactions = [];

    public function __construct(
        TransactionResource $transactionResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->transactionResource = $transactionResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(TransactionInterface $transaction): TransactionInterface
    {
        $this->transactionResource->save($transaction);
        return $transaction;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getItems();
    }

    public function getLastTransaction(string $incrementId): ?TransactionInterface
    {
        $transactions = $this->getAllTransactions($incrementId);
        if (!$transactions) {
            return null;
        }

        return current($transactions);
    }

    /**
     * If only "Authorize" is done, a transaction with status code
     * TransactionStatusInterface::PENDING_CAPTURE_CODE is made.
     *
     * If "Authorize and capture" is done: a transaction with status code
     * TransactionStatusInterface::CAPTURED_CODE is created
     *
     * @param string $incrementId
     * @return TransactionInterface|null
     */
    public function getAuthorizeTransaction(string $incrementId): ?TransactionInterface
    {
        if (!$transactions = $this->getAllTransactions($incrementId)) {
            return null;
        }

        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::PENDING_CAPTURE_CODE) {
                return $transaction;
            }
        }

        return $this->getCaptureTransaction($incrementId);
    }

    public function getCaptureTransaction(string $incrementId): ?TransactionInterface
    {
        $result = null;
        if (!$transactions = $this->getAllTransactions($incrementId)) {
            return null;
        }

        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::CAPTURED_CODE) {
                $result = $transaction;
                break;
            }
        }

        return $result;
    }

    public function getCaptureTransactionsAmount(string $incrementId): float
    {
        $amount = 0;
        if (!$transactions = $this->getAllTransactions($incrementId)) {
            return 0;
        }

        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::CAPTURED_CODE) {
                $amount += $transaction->getAmount();
            }
        }

        return $amount;
    }

    public function getRefundedTransactions(string $incrementId): array
    {
        $transactions = $this->getAllTransactions($incrementId);
        $result = [];
        if (!$transactions) {
            return $result;
        }

        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::REFUNDED_CODE) {
                $result[$transaction->getTransactionId()] = $transaction;
            }
        }

        return $result;
    }

    public function getRefundRejectedTransactions(string $incrementId): array
    {
        $transactions = $this->getAllTransactions($incrementId);
        $result = [];
        if (!$transactions) {
            return $result;
        }

        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::REFUND_REJECTED_CODE) {
                $result[$transaction->getTransactionId()] = $transaction;
            }
        }

        return $result;
    }

    public function getRefundedTransactionsAmount(string $incrementId): float
    {
        $refundAmount = 0;
        /** @var TransactionInterface $transaction */
        foreach ($this->getRefundedTransactions($incrementId) as $transaction) {
            $refundAmount += $transaction->getAmount();
        }

        return $refundAmount;
    }

    public function getPendingRefundTransactions(string $incrementId): array
    {
        $transactions = $this->getAllTransactions($incrementId);
        $result = [];
        if (!$transactions) {
            return $result;
        }

        $refundedTransaction = $this->getRefundedTransactions($incrementId);
        /** @var TransactionInterface $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() === TransactionStatusInterface::PENDING_REFUND_CODE
                && !array_key_exists($transaction->getTransactionId(), $refundedTransaction)
            ) {
                $result[$transaction->getTransactionId()] = $transaction;
            }
        }

        return $result;
    }

    public function getPendingRefundTransactionsAmount(string $incrementId): float
    {
        $pendingRefundAmount = 0;
        /** @var TransactionInterface $transaction */
        foreach ($this->getPendingRefundTransactions($incrementId) as $transaction) {
            $pendingRefundAmount += $transaction->getAmount();
        }

        return $pendingRefundAmount;
    }

    private function getAllTransactions(string $incrementId): array
    {
        if (!isset($this->transactions[$incrementId])) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(TransactionInterface::INCREMENT_ID, ['eq' => $incrementId]);
            $collection->getSelect()->order('main_table.entity_id DESC');

            $this->transactions[$incrementId] = $collection->getItems();
        }

        return $this->transactions[$incrementId];
    }
}
