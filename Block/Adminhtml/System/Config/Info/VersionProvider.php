<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl as CurlClient;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class VersionProvider
{
    private const EXTENSION_NAME = 'Worldline_PaymentCore';
    private const GITHUB_API =
        'https://api.github.com/repos/wl-online-payments-direct/plugin-magento-core/releases/latest';

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var CurlClient
     */
    private $curlClient;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PackageInfo $packageInfo,
        CurlClient $curlClient,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->packageInfo = $packageInfo;
        $this->curlClient = $curlClient;
        $this->json = $json;
        $this->logger = $logger;
    }

    public function getCurrentVersion(): string
    {
        return (string) $this->packageInfo->getVersion(self::EXTENSION_NAME);
    }

    public function getLatestVersion(): ?string
    {
        try {
            $this->curlClient->addHeader('user-agent', 'php');
            $this->curlClient->get(self::GITHUB_API);
            $response = $this->json->unserialize($this->curlClient->getBody());
            return $response['tag_name'] ?? null;
        } catch (LocalizedException $e) {
            $this->logger->warning($e->getMessage(), $e->getTrace());
            return null;
        }
    }
}
