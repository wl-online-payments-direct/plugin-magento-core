<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;

interface TransactionRepositoryInterface
{
    /**
     * @param TransactionInterface $transaction
     * @return TransactionInterface
     */
    public function save(TransactionInterface $transaction): TransactionInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TransactionInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * @param string $incrementId
     * @return TransactionInterface|null
     */
    public function getLastTransaction(string $incrementId): ?TransactionInterface;

    /**
     * @param string $incrementId
     * @return TransactionInterface|null
     */
    public function getAuthorizeTransaction(string $incrementId): ?TransactionInterface;

    /**
     * @param string $incrementId
     * @return TransactionInterface|null
     */
    public function getCaptureTransaction(string $incrementId): ?TransactionInterface;

    /**
     * @param string $incrementId
     * @return float
     */
    public function getCaptureTransactionsAmount(string $incrementId): float;

    /**
     * @param string $incrementId
     * @return array
     */
    public function getRefundedTransactions(string $incrementId): array;

    /**
     * @param string $incrementId
     * @return array
     */
    public function getRefundRejectedTransactions(string $incrementId): array;

    /**
     * @param string $incrementId
     * @return float
     */
    public function getRefundedTransactionsAmount(string $incrementId): float;

    /**
     * @param string $incrementId
     * @return array
     */
    public function getPendingRefundTransactions(string $incrementId): array;

    /**
     * @param string $incrementId
     * @return float
     */
    public function getPendingRefundTransactionsAmount(string $incrementId): float;
}
