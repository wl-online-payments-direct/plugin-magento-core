<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;

/**
 * Different format of payment_id from Worldline requests
 * ANZ - Australian payment provider
 *
 * Worldline returns two formats for the same transaction depending on endpoint:
 *  - HC API:        short inner ID (10 or 11 digits)
 *  - status/webhook: full ANZ-wrapped ID (19 or 20 digits) with `90000` prefix
 *
 * This formatter normalizes both forms to the short inner id so downstream
 * lookups and storage operate on a single canonical value.
 */
class PaymentIdFormatter implements PaymentIdFormatterInterface
{
    private const PAYMENT_ID_MIN_LENGTH = 10;
    private const PAYMENT_ID_MAX_LENGTH = 20;

    /**
     * Validate and format worldline payment ID
     *
     * @param string $wlPaymentId
     * @param bool $addPostfix
     * @return string
     * @throws LocalizedException
     */
    public function validateAndFormat(string $wlPaymentId, bool $addPostfix = false): string
    {
        $this->validate($wlPaymentId);

        $wlPaymentId = strstr($wlPaymentId, '_', true) ?: $wlPaymentId;

        $length = strlen($wlPaymentId);
        if ($length === self::PAYMENT_ID_MAX_LENGTH-1 || $length === self::PAYMENT_ID_MAX_LENGTH) {
            $wlPaymentId = preg_replace('/^900000?(\d+)\d{3}$/', '$1', $wlPaymentId);
        }

        return $addPostfix ? ($wlPaymentId . '_0') : $wlPaymentId;
    }

    /**
     * Validate payment ID
     *
     * @param string $wlPaymentId
     *
     * @throws LocalizedException
     */
    private function validate(string $wlPaymentId): void
    {
        if (!preg_match(
            '/^\d{' . self::PAYMENT_ID_MIN_LENGTH . ',' . self::PAYMENT_ID_MAX_LENGTH . '}(_\d+)?$/',
            $wlPaymentId
        )) {
            throw new LocalizedException(__('Incorrect worldline payment id'));
        }
    }
}
