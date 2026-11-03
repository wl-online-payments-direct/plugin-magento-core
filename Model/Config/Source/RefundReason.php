<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RefundReason implements OptionSourceInterface
{
    public const WRONG_AMOUNT_CORRECTION = 'WrongAmountCorrection';
    public const PRE_DISPUTE_REFUND = 'PreDisputeRefund';
    public const SUBSCRIPTION_REFUND = 'SubscriptionRefund';
    public const SERVICE_LATE_CANCELLATION = 'ServiceLateCancellation';
    public const OTHER = 'Other';
    public const DEFAULT_REASON = self::WRONG_AMOUNT_CORRECTION;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::WRONG_AMOUNT_CORRECTION, 'label' => self::WRONG_AMOUNT_CORRECTION],
            ['value' => self::PRE_DISPUTE_REFUND, 'label' => self::PRE_DISPUTE_REFUND],
            ['value' => self::SUBSCRIPTION_REFUND, 'label' => self::SUBSCRIPTION_REFUND],
            ['value' => self::SERVICE_LATE_CANCELLATION, 'label' => self::SERVICE_LATE_CANCELLATION],
            ['value' => self::OTHER, 'label' => self::OTHER]
        ];
    }

    public function isValid(string $reason): bool
    {
        return in_array($reason, array_column($this->toOptionArray(), 'value'), true);
    }
}
