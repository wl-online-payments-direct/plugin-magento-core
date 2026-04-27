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

    public function build(array $deviceData): CustomerDevice
    {
        $customerDevice = $this->customerDeviceFactory->create();
        $customerDevice->setAcceptHeader($deviceData['AcceptHeader'] ?? '');
        $customerDevice->setUserAgent($deviceData['UserAgent'] ?? '');
        $customerDevice->setLocale($deviceData['Locale'] ?? '');
        $customerDevice->setTimezoneOffsetUtcMinutes((string) ($deviceData['TimezoneOffsetUtcMinutes'] ?? ''));
        $customerDevice->setIpAddress($this->remoteAddress->getRemoteAddress() ?: null);

        $this->addBrowserData($customerDevice, $deviceData);

        return $customerDevice;
    }

    private function addBrowserData(CustomerDevice $customerDevice, array $deviceData): void
    {
        $browserData = $this->browserDataFactory->create();
        $browserData->setColorDepth((int) ($deviceData['BrowserData']['ColorDepth'] ?? 0));
        $browserData->setJavaEnabled((bool) ($deviceData['BrowserData']['JavaEnabled'] ?? false));
        $browserData->setScreenHeight((string) ($deviceData['BrowserData']['ScreenHeight'] ?? ''));
        $browserData->setScreenWidth((string) ($deviceData['BrowserData']['ScreenWidth'] ?? ''));

        $customerDevice->setBrowserData($browserData);
    }
}
