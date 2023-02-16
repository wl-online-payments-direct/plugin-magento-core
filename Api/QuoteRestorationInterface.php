<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

/**
 * Restore quote
 */
interface QuoteRestorationInterface
{
    public function preserveQuoteId(int $quoteId): void;

    public function shiftQuoteId(): void;

    public function restoreQuote(): void;
}
