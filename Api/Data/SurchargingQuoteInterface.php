<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * @method getQuoteId(): int
 * @method setQuoteId(int $quoteId): SurchargingQuoteInterface
 *
 * @method getPaymentMethod(): ?string
 * @method setPaymentMethod(string $method): SurchargingQuoteInterface
 *
 * @method getInvoiceId(): ?int
 * @method setInvoiceId(int $invoiceId): SurchargingQuoteInterface
 *
 * @method getAmount(): float
 * @method setAmount(float $amount): SurchargingQuoteInterface
 *
 * @method getBaseAmount(): float
 * @method setBaseAmount(float $baseAmount): SurchargingQuoteInterface
 *
 * @method getQuoteTotalAmount(): float
 * @method setQuoteTotalAmount(float $total): SurchargingQuoteInterface
 *
 * @method getIsInvoiced(): bool
 * @method setIsInvoiced(bool $isInvoiced): bool
 *
 * @method getIsRefunded(): bool
 * @method setIsRefunded(bool $isRefunded): bool
 */
interface SurchargingQuoteInterface
{
    public const QUOTE_ID = 'quote_id';
}
