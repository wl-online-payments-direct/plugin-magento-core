<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ThreeDSecure\Notification;

use Magento\Framework\FlagManager;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class AdminNotification implements MessageInterface
{
    public const FLAG_IDENTITY = 'worldline_moved_three_d_settings_notification';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(UrlInterface $urlBuilder, FlagManager $flagManager)
    {
        $this->urlBuilder = $urlBuilder;
        $this->flagManager = $flagManager;
    }

    public function getIdentity(): string
    {
        return self::FLAG_IDENTITY;
    }

    public function isDisplayed(): bool
    {
        return !$this->flagManager->getFlagData(self::FLAG_IDENTITY);
    }

    public function getText(): string
    {
        $messageDetails = __(
            'Please note that 3-D Secure and PWA settings can now be <a href="%1">configured globally</a>'
            . ' for all Worldline payment methods. Feel free to review these if needed.',
            $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/worldline_payment')
        );

        $messageDetails .= ' ';

        $messageDetails .= __(
            'Click on the link to <a href="%1">ignore this notification</a>',
            $this->urlBuilder->getUrl('worldline/system/ignoreMovedSettingsNotification')
        );

        return $messageDetails;
    }

    public function getSeverity(): int
    {
        return self::SEVERITY_CRITICAL;
    }
}
