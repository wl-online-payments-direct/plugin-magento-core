<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class GeneralSettingsConfig implements GeneralSettingsConfigInterface
{
    public const ENABLE_3D = 'worldline_payment/general_settings/enable_3d';
    public const ENFORCE_AUTH = 'worldline_payment/general_settings/enforce_authentication';
    public const AUTH_EXEMPTION = 'worldline_payment/general_settings/authentication_exemption';
    public const AUTH_EXEMPTION_TYPE = 'worldline_payment/general_settings/authentication_exemption_type';
    public const AUTH_LOW_VALUE_AMOUNT = 'worldline_payment/general_settings/authentication_exemption_limit_30';
    public const AUTH_TRANSACTION_RISK_ANALYSIS_AMOUNT =
        'worldline_payment/general_settings/authentication_exemption_limit_100';
    public const PWA_ROUTE = 'worldline_payment/general_settings/pwa_route';
    public const APPLY_SURCHARGE = 'worldline_payment/general_settings/apply_surcharge';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        State $appState,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->appState = $appState;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    public function isThreeDEnabled(?int $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENABLE_3D, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function isEnforceAuthEnabled(?int $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENFORCE_AUTH, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function isAuthExemptionEnabled(?int $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::AUTH_EXEMPTION, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function getAuthExemptionType(?int $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(self::AUTH_EXEMPTION_TYPE, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function getAuthLowValueAmount(?int $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(self::AUTH_LOW_VALUE_AMOUNT, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function saveAuthExemptionType(string $type): void
    {
        $this->configWriter->save(self::AUTH_EXEMPTION_TYPE, $type, ScopeInterface::SCOPE_STORE);
    }

    public function saveAuthLowValueAmount(string $amount): void
    {
        $this->configWriter->save(self::AUTH_LOW_VALUE_AMOUNT, $amount, ScopeInterface::SCOPE_STORE);
    }

    public function getAuthTransactionRiskAnalysisAmount(?int $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::AUTH_TRANSACTION_RISK_ANALYSIS_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function getReturnUrl(string $returnUrl, ?int $scopeCode = null): ?string
    {
        $pwaRoute = (string)$this->scopeConfig->getValue(
            self::PWA_ROUTE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        if ($pwaRoute && $this->appState->getAreaCode() === Area::AREA_GRAPHQL) {
            return $pwaRoute;
        }

        return $this->urlBuilder->getUrl($returnUrl);
    }

    public function isApplySurcharge(?int $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::APPLY_SURCHARGE, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function getValue(string $path, ?int $scopeCode = null): ?string
    {
        return (string)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $scopeCode);
    }
}
