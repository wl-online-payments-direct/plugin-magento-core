<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * @method getQuoteId(): int
 * @method setQuoteId(int $quoteId): SurchargingCreditmemoInterface
 *
 * @method getCreditmemoId(): int
 * @method setCreditmemoId(int $creditmemoId): SurchargingCreditmemoInterface
 *
 * @method getAmount(): float
 * @method setAmount(float $amount): SurchargingCreditmemoInterface
 *
 * @method getBaseAmount(): float
 * @method setBaseAmount(float $baseAmount): SurchargingCreditmemoInterface
 */
interface SurchargingCreditmemoInterface
{
    public const QUOTE_ID = 'quote_id';
    public const CREDITMEMO_ID = 'creditmemo_id';
}
