<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;

/**
 * Repository interface for worldline quote payment entity
 */
interface QuotePaymentRepositoryInterface
{
    /**
     * @param QuotePaymentInterface $quotePayment
     * @return QuotePaymentInterface
     */
    public function save(QuotePaymentInterface $quotePayment): QuotePaymentInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return QuotePaymentInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * @param int $paymentId
     * @return QuotePaymentInterface
     */
    public function get(int $paymentId): QuotePaymentInterface;

    /**
     * @param string $paymentIdentifier
     * @return QuotePaymentInterface
     */
    public function getByPaymentIdentifier(string $paymentIdentifier): QuotePaymentInterface;
}
