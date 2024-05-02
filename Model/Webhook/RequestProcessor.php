<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use OnlinePayments\Sdk\Domain\WebhooksEvent;
use OnlinePayments\Sdk\Webhooks\InMemorySecretKeyStoreFactory;
use OnlinePayments\Sdk\Webhooks\SignatureValidationException;
use OnlinePayments\Sdk\Webhooks\WebhooksHelperFactory;
use Worldline\PaymentCore\Model\Config\WebhookConfig;
use Worldline\PaymentCore\Api\QuoteResourceInterface;

class RequestProcessor
{
    /**
     * @var WebhookConfig
     */
    private $webhookConfig;

    /**
     * @var InMemorySecretKeyStoreFactory
     */
    private $inMemorySecretKeyStoreFactory;

    /**
     * @var WebhooksHelperFactory
     */
    private $webhooksHelperFactory;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    public function __construct(
        WebhookConfig $webhookConfig,
        InMemorySecretKeyStoreFactory $inMemorySecretKeyStoreFactory,
        WebhooksHelperFactory $webhooksHelperFactory,
        QuoteResourceInterface $quoteResource
    ) {
        $this->webhookConfig = $webhookConfig;
        $this->inMemorySecretKeyStoreFactory = $inMemorySecretKeyStoreFactory;
        $this->webhooksHelperFactory = $webhooksHelperFactory;
        $this->quoteResource = $quoteResource;
    }

    public function getWebhookEvent(string $body, string $signature, string $keyId): ?WebhooksEvent
    {
        $storeId = $this->getStoreId($body);

        $secretKeyStore = $this->inMemorySecretKeyStoreFactory->create([
            'secretKeys' => [
                $this->webhookConfig->getKey($storeId) => $this->webhookConfig->getSecretKey($storeId)
            ]
        ]);
        $helper = $this->webhooksHelperFactory->create(['secretKeyStore' => $secretKeyStore]);

        try {
            return $helper->unmarshal($body, [
                'X-GCS-Signature' => $signature,
                'X-GCS-KeyId' => $keyId
            ]);
        } catch (SignatureValidationException $e) {
            return null;
        }
    }

    private function getStoreId(string $body): ?int
    {
        $incrementId = $this->extractIncrementId($body);
        if (!$incrementId) {
            return null;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote) {
            return null;
        }

        return (int) $quote->getStoreId();
    }

    private function extractIncrementId(string $body): ?string
    {
        preg_match('/"merchantReference":"(\d*)/', $body, $result);
        return $result[1] ?? null;
    }
}
