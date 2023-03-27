<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Config;

interface GeneralSettingsConfigInterface
{
    public function isThreeDEnabled(?int $scopeCode = null): bool;

    public function isEnforceAuthEnabled(?int $scopeCode = null): bool;

    public function isAuthExemptionEnabled(?int $scopeCode = null): bool;

    public function getReturnUrl(string $returnUrl, ?int $scopeCode = null): string;

    public function isApplySurcharge(?int $scopeCode = null): bool;

    public function getValue(string $path, ?int $scopeCode = null): string;
}
