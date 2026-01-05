<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class WebhookConfig extends AbstractHelper
{
    public const WEBHOOK_MODE = 'worldline_connection/webhook/webhook_mode';
    public const ADDITIONAL_WEBHOOK_URLS = 'worldline_connection/webhook/additional_webhook_urls';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context             $context,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    public function isAutomaticMode(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::WEBHOOK_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdditionalWebhookUrls(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::ADDITIONAL_WEBHOOK_URLS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($value)) {
            return [];
        }

        try {
            $urls = $this->serializer->unserialize($value);
            return array_filter($urls, function ($url) {
                return !empty(trim($url));
            });
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllWebhookUrls(string $baseUrl, ?int $storeId = null): array
    {
        if (!$this->isAutomaticMode($storeId)) {
            return [];
        }

        $urls = [$baseUrl . 'worldline/webhook'];
        $additionalUrls = $this->getAdditionalWebhookUrls($storeId);

        return array_merge($urls, $additionalUrls);
    }
}
