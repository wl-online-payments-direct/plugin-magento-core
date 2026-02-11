<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ThreeDSecure;

use OnlinePayments\Sdk\Domain\ThreeDSecure;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;

class ParamsHandler
{
    public const NONE_EXEMPTION_TYPE = 'none';
    public const LOW_VALUE_EXEMPTION_TYPE = 'low-value';
    public const TRANSACTION_RISK_ANALYSIS_EXEMPTION_TYPE = 'transaction-risk-analysis';
    public const ANALYSIS_PERFORMED_CHALLENGE_INDICATOR = 'no-challenge-requested-risk-analysis-performed';
    public const NO_CHALLENGE_REQUESTED_CHALLENGE_INDICATOR = 'no-challenge-requested';

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

        $isAuthExemptionEnabled = $this->generalSettings->isAuthExemptionEnabled($storeId);
        $threeDSExemptedType = $this->generalSettings->getAuthExemptionType($storeId);

        $threeDSExemptedAmount = $this->getExemptedAmount($threeDSExemptedType, $storeId);

        if ($isAuthExemptionEnabled && (float)$threeDSExemptedAmount >= $baseSubtotal) {
            $threeDSecure->setSkipAuthentication(false);
            $threeDSecure->setExemptionRequest($threeDSExemptedType);
            $threeDSecure->setSkipSoftDecline(false);
            $threeDSecure->setChallengeIndicator($this->resolveChallengeIndicator($threeDSExemptedType));
        }

        if ($this->generalSettings->isEnforceAuthEnabled($storeId)) {
            $threeDSecure->setChallengeIndicator('challenge-required');
        }
    }

    /**
     * @param string $type
     * @param int $storeId
     *
     * @return string
     */
    private function getExemptedAmount(string $type, int $storeId): string
    {
        switch ($type) {
            case self::NONE_EXEMPTION_TYPE:
                return $this->generalSettings->getAuthNoChallengeAmount($storeId);

            case self::LOW_VALUE_EXEMPTION_TYPE:
                return $this->generalSettings->getAuthLowValueAmount($storeId);

            case self::TRANSACTION_RISK_ANALYSIS_EXEMPTION_TYPE:
                return $this->generalSettings->getAuthTransactionRiskAnalysisAmount($storeId);

            default:
                return "0";
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function resolveChallengeIndicator(string $type): string
    {
        return $type === self::TRANSACTION_RISK_ANALYSIS_EXEMPTION_TYPE
            ? self::ANALYSIS_PERFORMED_CHALLENGE_INDICATOR
            : self::NO_CHALLENGE_REQUESTED_CHALLENGE_INDICATOR;
    }
}
