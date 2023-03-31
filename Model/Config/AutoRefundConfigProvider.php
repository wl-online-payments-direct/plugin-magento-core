<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provide values from system configuration
 */
class AutoRefundConfigProvider
{
    public const IS_ENABLED = 'worldline_order_creator/auto_refund/active';
    public const SENDER = 'worldline_order_creator/auto_refund/sender';
    public const RECIPIENT = 'worldline_order_creator/auto_refund/recipient';
    public const EMAIL_TEMPLATE = 'worldline_order_creator/auto_refund/email_template';

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

    public function getEmailTemplate(): string
    {
        return (string) $this->scopeConfig->getValue(self::EMAIL_TEMPLATE);
    }
}
