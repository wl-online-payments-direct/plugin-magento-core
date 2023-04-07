<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure;

use Magento\Framework\App\Request\HttpFactory as HttpRequestFactory;
use Magento\Framework\Controller\ResultInterface;
use Worldline\PaymentCore\Api\Test\Infrastructure\WebhookStubSenderInterface;
use Worldline\PaymentCore\Controller\Webhook\IndexFactory as WebhookControllerFactory;

class WebhookStubSender implements WebhookStubSenderInterface
{
    /**
     * @var WebhookControllerFactory
     */
    private $webhookControllerFactory;

    /**
     * @var HttpRequestFactory
     */
    private $httpRequestFactory;

    public function __construct(
        WebhookControllerFactory $webhookControllerFactory,
        HttpRequestFactory $httpRequestFactory
    ) {
        $this->webhookControllerFactory = $webhookControllerFactory;
        $this->httpRequestFactory = $httpRequestFactory;
    }

    public function sendWebhook(string $content, array $headers = []): ResultInterface
    {
        if (!$headers) {
            $expectedSignature = base64_encode(hash_hmac("sha256", $content, 'test-X-Gcs-Signature', true));
            $headers = [
                'X-Gcs-Signature' => $expectedSignature,
                'X-Gcs-Keyid' => 'test-X-Gcs-Keyid',
            ];
        }

        $request = $this->httpRequestFactory->create();
        $request->getHeaders()->addHeaders($headers);
        $request->setContent($content);

        return $this->webhookControllerFactory->create(['request' => $request])->execute();
    }
}
