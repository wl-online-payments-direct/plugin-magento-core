<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest;

use Exception;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\Feedbacks;
use OnlinePayments\Sdk\Domain\FeedbacksFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\FeedbacksDataBuilderInterface;
use Worldline\PaymentCore\Model\WebhookConfig;

class FeedbacksDataBuilder implements FeedbacksDataBuilderInterface
{
    /**
     * @var WebhookConfig
     */
    private $webhookConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FeedbacksFactory
     */
    private $feedbacksFactory;

    /**
     * @param WebhookConfig $webhookConfig
     * @param StoreManagerInterface $storeManager
     * @param FeedbacksFactory $feedbacksFactory
     */
    public function __construct(
        WebhookConfig $webhookConfig,
        StoreManagerInterface $storeManager,
        FeedbacksFactory $feedbacksFactory
    ) {
        $this->webhookConfig = $webhookConfig;
        $this->storeManager = $storeManager;
        $this->feedbacksFactory = $feedbacksFactory;
    }

    public function build(CartInterface $quote): ?Feedbacks
    {
        try {
            $storeId = (int)$quote->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $baseUrl = $store->getBaseUrl();

            $urls = $this->webhookConfig->getAllWebhookUrls($baseUrl, $storeId);

            if (empty($urls)) {
                return null;
            }

            $feedbacks = $this->feedbacksFactory->create();
            $feedbacks->setWebhooksUrls($urls);

            return $feedbacks;

        } catch (Exception $e) {
            return null;
        }
    }
}
