<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Context interface for the validator pool
 *
 * @method getStatusCode(): ?int
 * @method setStatusCode(int $statusCode): CanPlaceOrderContextInterface
 * @method getWorldlinePaymentId(): ?string
 * @method setWorldlinePaymentId(string $worldlinePaymentId): CanPlaceOrderContextInterface
 * @method getIncrementId(): ?string
 * @method setIncrementId(?string $incrementId): CanPlaceOrderContextInterface
 */
interface CanPlaceOrderContextInterface
{

}
