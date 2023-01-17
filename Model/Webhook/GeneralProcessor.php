<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Model\Order\CommentManager as OrderCommentManager;

class GeneralProcessor implements ProcessorInterface
{
    /**
     * @var WebhookLogger
     */
    private $webhookLogger;

    /**
     * @var OrderCommentManager
     */
    private $orderCommentManager;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(
        WebhookLogger $webhookLogger,
        OrderCommentManager $orderCommentManager,
        array $processors = []
    ) {
        $this->processors = $processors;
        $this->webhookLogger = $webhookLogger;
        $this->orderCommentManager = $orderCommentManager;
    }

    /**
     * Log webhook data into table, process webhook data, add comment to an order
     *
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent): void
    {
        $this->webhookLogger->logFromEvent($webhookEvent);

        $processor = $this->processors[$webhookEvent->getType()] ?? false;
        if (!$processor) {
            $this->orderCommentManager->addComment($webhookEvent);
            return;
        }

        $processor->process($webhookEvent);
        $this->orderCommentManager->addComment($webhookEvent);
    }
}
