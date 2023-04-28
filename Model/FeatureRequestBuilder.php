<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\PackageInfo;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * Prepare feature request query
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class FeatureRequestBuilder
{
    private const SEND_TO = 'DL_ShoppingCarts@ingenico.com';
    private const EXTENSION_NAME = 'Worldline_PaymentCore';

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        Session $authSession,
        PackageInfo $packageInfo,
        EmailSender $emailSender,
        WorldlineConfig $worldlineConfig,
        ProductMetadataInterface $productMetadata
    ) {
        $this->authSession = $authSession;
        $this->packageInfo = $packageInfo;
        $this->emailSender = $emailSender;
        $this->worldlineConfig = $worldlineConfig;
        $this->productMetadata = $productMetadata;
    }

    public function build(int $storeId, string $companyName, string $message, ?string $pspid = null): void
    {
        if (!$this->authSession->getUser()) {
            return;
        }

        if (!$pspid) {
            $pspid = $this->worldlineConfig->getMerchantId($storeId);
        }

        $adminUserEmail = (string)$this->authSession->getUser()->getEmail();
        $adminUserName = (string)$this->authSession->getUser()->getUserName();
        $magentoVersion = (string)$this->productMetadata->getVersion();
        $pluginVersion = (string)$this->packageInfo->getVersion(self::EXTENSION_NAME);

        $emailBody = __('Company Name: %1', $companyName) . "\n"
            . __('Message: %1', $message) . "\n"
            . __('PSPID: %1', $pspid) . "\n"
            . __('Magento version: %1', $magentoVersion) . "\n"
            . __('Plugin version: %1', $pluginVersion);

        $this->emailSender->sendEmailWithoutTemplate($emailBody, $adminUserEmail, $adminUserName, self::SEND_TO);
    }
}
