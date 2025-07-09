<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\WebhookInterfaceFactory as WebhookFactory;
use Worldline\PaymentCore\Model\Config\DebugConfig;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook as WebhookResource;

/**
 * Logger for worldline webhook entity
 */
class WebhookLogger
{
    /**
     * @var WebhookFactory
     */
    private $webhookFactory;

    /**
     * @var WebhookResource
     */
    private $webhookResource;

    /**
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DebugConfig
     */
    private $debugConfig;

    public function __construct(
        WebhookFactory $webhookFactory,
        WebhookResource $webhookResource,
        WebhookResponseManager $webhookResponseManager,
        LoggerInterface $logger,
        DebugConfig $debugConfig
    ) {
        $this->webhookResource = $webhookResource;
        $this->webhookResponseManager = $webhookResponseManager;
        $this->webhookFactory = $webhookFactory;
        $this->logger = $logger;
        $this->debugConfig = $debugConfig;
    }

    public function logFromEvent(WebhooksEvent $webhookEvent): void
    {
        if (!$this->debugConfig->isWebhookLogEnabled()) {
            return;
        }

        $response = $this->webhookResponseManager->getResponse($webhookEvent);
        $webhookEntity = $this->webhookFactory->create();

        try {
            $webhookEntity->setType((string) $webhookEvent->type);
            $webhookEntity->setStatusCode((int) $response->getStatusOutput()->getStatusCode());
            if ($response instanceof PaymentResponse) {
                $webhookEntity->setIncrementId(
                    (string) $response->getPaymentOutput()->getReferences()->getMerchantReference()
                );
            } elseif ($response instanceof RefundResponse) {
                $webhookEntity->setIncrementId(
                    (string) $response->getRefundOutput()->getReferences()->getMerchantReference()
                );
            }
            $webhookEntity->setBody((string) $webhookEvent->toJson());

            $this->webhookResource->save($webhookEntity);
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        }
    }
}
