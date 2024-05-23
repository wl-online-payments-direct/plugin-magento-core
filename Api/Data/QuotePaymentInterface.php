<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Worldline quote payment details
 *
 * @method getPaymentId(): int
 * @method setPaymentId(int $paymentId): QuotePaymentInterface
 *
 * @method getPaymentIdentifier(): string
 * @method setPaymentIdentifier(string $paymentIdentifier): QuotePaymentInterface
 *
 * @method getMethod(): string
 * @method setMethod(string $method): QuotePaymentInterface
 *
 * @method getDeviceData(): string
 * @method setDeviceData(string $deviceData): QuotePaymentInterface
 *
 * @method getPublicHash(): string
 * @method setPublicHash(string $publicHash): QuotePaymentInterface
 */
interface QuotePaymentInterface
{
    public const PAYMENT_ID = 'payment_id';
    public const PAYMENT_IDENTIFIER = 'payment_identifier';
    public const DEVICE_DATA = 'device_data';
}
