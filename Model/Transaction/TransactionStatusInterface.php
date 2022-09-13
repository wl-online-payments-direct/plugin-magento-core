<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

interface TransactionStatusInterface
{
    public const PENDING_CAPTURE_CODE = 5;
    public const CAPTURE_REQUESTED = 4;
    public const CAPTURED_CODE = 9;

    public const PENDING_REFUND_CODE = 81;
    public const REFUNDED_CODE = 8;
}
