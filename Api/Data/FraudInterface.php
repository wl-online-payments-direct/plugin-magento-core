<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Worldline fraud information entity
 *
 * @method getWorldlinePaymentId(): int
 * @method setWorldlinePaymentId(int $paymentId): FraudInterface
 *
 * @method getResult(): ?string
 * @method setResult(string $result): FraudInterface
 *
 * @method getLiability(): ?string
 * @method setLiability(string $liability): FraudInterface
 *
 * @method getExemption(): ?string
 * @method setExemption(string $exemption): FraudInterface
 *
 * @method getAuthenticationStatus(): ?string
 * @method setAuthenticationStatus(string $authenticationStatus): FraudInterface
 */
interface FraudInterface
{
    public const WORLDLINE_PAYMENT_ID = 'worldline_payment_id';
    public const RESULT = 'result';
    public const LIABILITY = 'liability';
    public const EXEMPTION = 'exemption';
    public const AUTHENTICATION_STATUS = 'authentication_status';
}
