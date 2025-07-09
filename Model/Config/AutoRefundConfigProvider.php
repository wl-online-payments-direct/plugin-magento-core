<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provide values from system configuration
 */
class AutoRefundConfigProvider
{
    public const IS_ENABLED = 'worldline_order_creator/auto_refund/active';
    public const SENDER = 'worldline_order_creator/auto_refund/sender';
    public const RECIPIENT = 'worldline_order_creator/auto_refund/recipient';
    public const EMAIL_COPY_TO = 'worldline_order_creator/auto_refund/copy_to';
    public const EMAIL_TEMPLATE = 'worldline_order_creator/auto_refund/email_template';
    public const IS_ENABLED_TO_CUSTOMER = 'worldline_order_creator/auto_refund/active_to_customer';
    public const SENDER_TO_CUSTOMER = 'worldline_order_creator/auto_refund/to_customer_sender';
    public const EMAIL_TEMPLATE_TO_CUSTOMER = 'worldline_order_creator/auto_refund/to_customer_template';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_ENABLED);
    }

    public function getSender(): string
    {
        return (string) $this->scopeConfig->getValue(self::SENDER);
    }

    public function getRecipient(): string
    {
        return (string) $this->scopeConfig->getValue(self::RECIPIENT);
    }

    public function getEmailCopyTo(): string
    {
        return (string) $this->scopeConfig->getValue(self::EMAIL_COPY_TO);
    }

    public function getEmailTemplate(): string
    {
        return (string) $this->scopeConfig->getValue(self::EMAIL_TEMPLATE);
    }

    public function isEnabledToCustomer(?int $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_ENABLED_TO_CUSTOMER, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getSenderToCustomer(?int $store = null): string
    {
        return (string) $this->scopeConfig->getValue(self::SENDER_TO_CUSTOMER, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getEmailTemplateToCustomer(?int $store = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE_TO_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
