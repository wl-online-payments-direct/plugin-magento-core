<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;

/**
 * Repository interface for worldline payment entity
 */
interface PaymentRepositoryInterface
{
    /**
     * @param PaymentInterface $payment
     * @return PaymentInterface
     */
    public function save(PaymentInterface $payment): PaymentInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return PaymentInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * @param string $incrementId
     * @return PaymentInterface
     */
    public function get(string $incrementId): PaymentInterface;

    /**
     * @param string $incrementId
     * @return void
     */
    public function deleteByIncrementId(string $incrementId): void;
}
