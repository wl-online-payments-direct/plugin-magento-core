<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Webhook\CustomProcessorStrategyInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;
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

    /**
     * @var CustomProcessorStrategyInterface[]
     */
    private $customProcessorStrategies;

    public function __construct(
        WebhookLogger $webhookLogger,
        OrderCommentManager $orderCommentManager,
        array $processors = [],
        array $customProcessorStrategies = []
    ) {
        $this->webhookLogger = $webhookLogger;
        $this->orderCommentManager = $orderCommentManager;
        $this->processors = $processors;
        $this->customProcessorStrategies = $customProcessorStrategies;
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

        if (!$processor = $this->getProcessor($webhookEvent)) {
            $this->orderCommentManager->addComment($webhookEvent);
            return;
        }

        $processor->process($webhookEvent);
        $this->orderCommentManager->addComment($webhookEvent);
    }

    private function getProcessor(WebhooksEvent $webhookEvent)
    {
        foreach ($this->customProcessorStrategies as $strategy) {
            if ($processor = $strategy->getProcessor($webhookEvent)) {
                return $processor;
            }
        }

        return $this->processors[$webhookEvent->type] ?? null;
    }
}
