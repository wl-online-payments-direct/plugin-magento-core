<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\SurchargingQuoteInterface;

interface SurchargingQuoteRepositoryInterface
{
    public function save(SurchargingQuoteInterface $surchargingQuoteEntity): SurchargingQuoteInterface;

    /**
     * Returns the active row (deleted_at IS NULL) for the given quote id.
     * Used by checkout-time consumers (GraphQL cart info, surcharge calculation, payment request building).
     */
    public function getByQuoteId(int $quoteId): SurchargingQuoteInterface;

    /**
     * Returns the row for the given quote id regardless of deleted_at state.
     * Used by post-order consumers (invoice / creditmemo flows, PDF rendering,
     * order/invoice/creditmemo block renderers).
     */
    public function getByQuoteIdIncludingDeleted(int $quoteId): SurchargingQuoteInterface;

    /**
     * Resolves the order's quote id, then returns the surcharge row regardless of deleted_at state.
     */
    public function getByOrderId(int $orderId): SurchargingQuoteInterface;

    public function deleteByQuoteId(int $quoteId): void;
}
