<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;

class DeviceDataAssigner implements DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        $payment->setAdditionalInformation(
            'device',
            [
                'AcceptHeader' => $additionalInformation['agent'] ?? '',
                'UserAgent' => $additionalInformation['user-agent'] ?? '',
                'Locale' => $additionalInformation['locale'] ?? '',
                'TimezoneOffsetUtcMinutes' => $additionalInformation['timezone_offset_utc_minutes'] ?? '',
                'BrowserData' => [
                    'ColorDepth' => $additionalInformation['color_depth'] ?? '',
                    'JavaEnabled' => (bool) ($additionalInformation['java_enabled'] ?? false),
                    'ScreenHeight' => $additionalInformation['screen_height'] ?? '',
                    'ScreenWidth' => $additionalInformation['screen_width'] ?? '',
                ],
            ]
        );
    }
}
