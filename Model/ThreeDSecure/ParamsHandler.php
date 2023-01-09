<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ThreeDSecure;

use OnlinePayments\Sdk\Domain\ThreeDSecure;

class ParamsHandler
{
    public const THRESHOLD_VALUE = 30;

    public function handle(
        ThreeDSecure $threeDSecure,
        float $baseSubtotal,
        bool $isThreeDExemptionEnabled,
        bool $isAuthenticationTriggerEnabled
    ): void {
        if ($isThreeDExemptionEnabled && $baseSubtotal < self::THRESHOLD_VALUE) {
            $threeDSecure->setExemptionRequest('low-value');
            return;
        }

        if ($isAuthenticationTriggerEnabled) {
            $threeDSecure->setChallengeIndicator('challenge-required');
        }
    }
}
