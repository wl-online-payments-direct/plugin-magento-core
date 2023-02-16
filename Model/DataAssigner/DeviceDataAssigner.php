<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Assign device data to the payment
 */
class DeviceDataAssigner implements DataAssignerInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        $payment->setAdditionalInformation(
            'device',
            [
                'AcceptHeader' => (string) $this->request->getHeader('accept'),
                'UserAgent' => (string) $this->request->getHeader('user-agent'),
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
