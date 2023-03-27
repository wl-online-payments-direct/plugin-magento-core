<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;

/**
 * Different format of payment_id from Worldline requests
 * ANZ - Australian payment provider
 */
class PaymentIdFormatter implements PaymentIdFormatterInterface
{
    private const PAYMENT_ID_STANDARD_LENGTH = 10;
    private const PAYMENT_ID_ANZ_LENGTH = 19;

    /**
     * Validate and format worldline payment id
     *
     * @param string $wlPaymentId
     * @param bool $addPostfix
     * @return string
     * @throws LocalizedException
     */
    public function validateAndFormat(string $wlPaymentId, bool $addPostfix = false): string
    {
        $this->validate($wlPaymentId);

        if (strlen($wlPaymentId) === self::PAYMENT_ID_ANZ_LENGTH) {
            $wlPaymentId = mb_substr($wlPaymentId, 6, self::PAYMENT_ID_STANDARD_LENGTH);
        }

        return $addPostfix ? ((int)$wlPaymentId . '_0') : ((string)(int)$wlPaymentId);
    }

    /**
     * Validate payment id
     *
     * @param string $wlPaymentId
     * @return void
     * @throws LocalizedException
     */
    private function validate(string $wlPaymentId): void
    {
        $isValid = in_array(
            strlen($wlPaymentId),
            [self::PAYMENT_ID_STANDARD_LENGTH, self::PAYMENT_ID_ANZ_LENGTH]
        ) || strpos($wlPaymentId, '_');

        if (!$isValid) {
            throw new LocalizedException(__('Incorrect worldline payment id'));
        }
    }
}
