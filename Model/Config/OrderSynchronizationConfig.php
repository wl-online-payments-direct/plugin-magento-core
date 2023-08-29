<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class OrderSynchronizationConfig
{
    public const SENDING_REFUSED_EMAILS = 'worldline_order_creator/general/sending_payment_refused_emails';
    public const REFUSED_PAYMENT_SENDER = 'worldline_order_creator/general/refused_payment_sender';
    public const REFUSED_PAYMENT_TEMPLATE = 'worldline_order_creator/general/refused_payment_template';
    public const FALLBACK_TIMEOUT = 'worldline_order_creator/general/fallback_timeout';
    public const FALLBACK_TIMEOUT_LIMIT = 'worldline_order_creator/general/fallback_timeout_limit';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getFallbackTimeout(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(self::FALLBACK_TIMEOUT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getFallbackTimeoutLimit(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(self::FALLBACK_TIMEOUT_LIMIT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isPaymentRefusedEmailsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::SENDING_REFUSED_EMAILS, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRefusedPaymentTemplate(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(self::REFUSED_PAYMENT_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRefusedPaymentSender(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(self::REFUSED_PAYMENT_SENDER, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
