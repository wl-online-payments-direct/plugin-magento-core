<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

interface EmailSendingListInterface
{
    public const ENTITY_ID = 'entity_id';
    public const INCREMENT_ID = 'increment_id';
    public const LEVEL = 'level';

    public const FAILED_ORDER = 'failed_order';
    public const PAYMENT_REFUSED = 'payment_refused';

    public function getId();

    public function getIncrementId(): string;
    public function setIncrementId(string $incrementId): EmailSendingListInterface;

    public function getLevel(): string;
    public function setLevel(string $level): EmailSendingListInterface;
}
