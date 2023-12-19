<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

/**
 * Interface container for worldline statuses
 * @link https://docs.direct.worldline-solutions.com/en/integration/api-developer-guide/statuses
 */
interface TransactionStatusInterface
{
    // Create order statuses
    public const PENDING_CAPTURE_CODE = 5;
    public const CAPTURE_REQUESTED = 4;
    public const CAPTURED_CODE = 9;

    // Refunded statuses
    public const PENDING_REFUND_CODE = 81;
    public const REFUNDED_CODE = 8;
    public const REFUND_UNCERTAIN_CODE = 82;
    public const REFUND_REJECTED_CODE = 83;

    // Failed payment statuses
    public const CANCELLED_BY_CUSTOMER = 1;
    public const AUTHORISATION_DECLINED = 2;
    public const AUTHORISED_AND_CANCELLED = 6;
    public const PAYMENT_REFUSED = 93;

    // Waiting payment statuses
    public const WAITING_AUTHENTICATION = 46;
    public const PAYMENT_PROCESSING = 91;
    public const PAYMENT_UNCERTAIN = 92;
    public const BEING_PROCESSED = 99;
}
