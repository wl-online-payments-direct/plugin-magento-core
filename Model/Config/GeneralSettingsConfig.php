<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class GeneralSettingsConfig
{
    public const ENABLE_3D = 'worldline_payment/general_settings/enable_3d';
    public const ENFORCE_AUTH = 'worldline_payment/general_settings/enforce_authentication';
    public const AUTH_EXEMPTION = 'worldline_payment/general_settings/authentication_exemption';
    public const PWA_ROUTE = 'worldline_payment/general_settings/pwa_route';

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

    public function __construct(State $appState, UrlInterface $urlBuilder, ScopeConfigInterface $scopeConfig)
    {
        $this->appState = $appState;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
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

    public function getReturnUrl(string $returnUrl, ?int $scopeCode = null): string
    {
        $pwaRoute = (string)$this->scopeConfig->getValue(self::PWA_ROUTE, ScopeInterface::SCOPE_STORE, $scopeCode);
        if ($pwaRoute && $this->appState->getAreaCode() === Area::AREA_GRAPHQL) {
            return $pwaRoute;
        }

        return $this->urlBuilder->getUrl($returnUrl);
    }
}
