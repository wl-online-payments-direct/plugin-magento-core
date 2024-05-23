<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\DataAssigner;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;

/**
 * Assign device data to the payment
 */
class DeviceDataAssigner implements DataAssignerInterface
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Json $jsonSerializer,
        RequestInterface $request
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->request = $request;
    }

    public function assign(
        PaymentInterface $payment,
        QuotePaymentInterface $wlQuotePayment,
        array $additionalInformation
    ): void {
        $deviceData = [
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
         ];

        $payment->setAdditionalInformation('payment_id', '');
        $payment->setAdditionalInformation('device_data', $deviceData);
        $wlQuotePayment->setPaymentId((int)$payment->getId());
        $wlQuotePayment->setDeviceData($this->jsonSerializer->serialize($deviceData));
    }
}
