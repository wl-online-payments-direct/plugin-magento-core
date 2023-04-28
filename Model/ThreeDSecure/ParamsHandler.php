<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ThreeDSecure;

use OnlinePayments\Sdk\Domain\ThreeDSecure;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;

class ParamsHandler
{
    public const THRESHOLD_VALUE = 30;

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    public function __construct(GeneralSettingsConfigInterface $generalSettings)
    {
        $this->generalSettings = $generalSettings;
    }

    public function handle(ThreeDSecure $threeDSecure, float $baseSubtotal, int $storeId): void
    {
        $isThreeDEnabled = $this->generalSettings->isThreeDEnabled($storeId);
        $threeDSecure->setSkipAuthentication(!$isThreeDEnabled);

        if (!$isThreeDEnabled) {
            return;
        }

        if ($baseSubtotal < self::THRESHOLD_VALUE && $this->generalSettings->isAuthExemptionEnabled($storeId)) {
            $threeDSecure->setExemptionRequest('low-value');
            return;
        }

        if ($this->generalSettings->isEnforceAuthEnabled($storeId)) {
            $threeDSecure->setChallengeIndicator('challenge-required');
        }
    }
}
