<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Payment;

use Magento\Framework\Exception\LocalizedException;

/**
 * Different format of payment_id from Worldline requests
 */
interface PaymentIdFormatterInterface
{
    /**
     * @param string $wlPaymentId
     * @param bool $addPostfix
     * @return string
     *
     * @throw LocalizedException
     */
    public function validateAndFormat(string $wlPaymentId, bool $addPostfix = false): string;
}
