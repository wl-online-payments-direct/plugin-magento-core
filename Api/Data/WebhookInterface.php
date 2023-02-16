<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Worldline webhook entity
 *
 * @method getIncrementId(): string
 * @method setIncrementId(sting $incrementId): WebhookInterface
 *
 * @method getType(): ?string
 * @method setType(string $type): WebhookInterface
 *
 * @method getStatusCode(): ?int
 * @method setStatusCode(int $statusCode): WebhookInterface
 *
 * @method getBody(): ?string
 * @method setBody(string $body): WebhookInterface
 */
interface WebhookInterface
{
    public const INCREMENT_ID = 'increment_id';
    public const TYPE = 'type';
    public const STATUS_CODE = 'status_code';
    public const BODY = 'body';
    public const CREATED_AT = 'created_at';
}
