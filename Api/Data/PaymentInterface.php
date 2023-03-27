<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Worldline payment details
 *
 * @method getIncrementId(): ?string
 * @method setIncrementId(string $incrementId): PaymentInterface
 *
 * @method getPaymentId(): string
 * @method setPaymentId(string $paymentId): PaymentInterface
 *
 * @method getPaymentProductId(): int
 * @method setPaymentProductId(int $paymentProductId): PaymentInterface
 *
 * @method getAmount(): int
 * @method setAmount(int $amount): PaymentInterface
 *
 * @method getCurrency(): string
 * @method setCurrency(string $currency): PaymentInterface
 *
 * @method getFraudResult(): string
 * @method setFraudResult(string $fraudResult): PaymentInterface
 *
 * @method getCardNumber(): string
 * @method setCardNumber(string $cardNumber): PaymentInterface
 *
 * @method getCreatedAt(): string
 */
interface PaymentInterface
{
    public const INCREMENT_ID = 'increment_id';
    public const PAYMENT_ID = 'payment_id';
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';
}
