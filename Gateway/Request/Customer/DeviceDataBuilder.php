<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Request\Customer;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use OnlinePayments\Sdk\Domain\BrowserDataFactory;
use OnlinePayments\Sdk\Domain\CustomerDevice;
use OnlinePayments\Sdk\Domain\CustomerDeviceFactory;

class DeviceDataBuilder
{
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var BrowserDataFactory
     */
    private $browserDataFactory;

    /**
     * @var CustomerDeviceFactory
     */
    private $customerDeviceFactory;

    public function __construct(
        RemoteAddress $remoteAddress,
        BrowserDataFactory $browserDataFactory,
        CustomerDeviceFactory $customerDeviceFactory
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->browserDataFactory = $browserDataFactory;
        $this->customerDeviceFactory = $customerDeviceFactory;
    }

    /**
     * @param array $deviceData
     * @return CustomerDevice
     */
    public function build(array $deviceData): CustomerDevice
    {
        $customerDevice = $this->customerDeviceFactory->create();
        $customerDevice->setAcceptHeader($deviceData['AcceptHeader'] ?? '');
        $customerDevice->setUserAgent($deviceData['UserAgent'] ?? '');
        $customerDevice->setLocale($deviceData['Locale'] ?? '');
        $customerDevice->setTimezoneOffsetUtcMinutes($deviceData['TimezoneOffsetUtcMinutes'] ?? '');
        $customerDevice->setIpAddress($this->remoteAddress->getRemoteAddress());

        $this->addBrowserData($customerDevice, $deviceData);

        return $customerDevice;
    }

    /**
     * @param CustomerDevice $customerDevice
     * @param array $deviceData
     * @return void
     */
    private function addBrowserData(CustomerDevice $customerDevice, array $deviceData)
    {
        $browserData = $this->browserDataFactory->create();
        $browserData->setColorDepth($deviceData['BrowserData']['ColorDepth'] ?? '');
        $browserData->setJavaEnabled((bool) ($deviceData['BrowserData']['JavaEnabled'] ?? false));
        $browserData->setScreenHeight($deviceData['BrowserData']['ScreenHeight'] ?? '');
        $browserData->setScreenWidth($deviceData['BrowserData']['ScreenWidth'] ?? '');

        $customerDevice->setBrowserData($browserData);
    }
}
