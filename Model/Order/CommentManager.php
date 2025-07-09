<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Webhook\WebhookResponseManager;

/**
 * Format and save comment to the order
 */
class CommentManager
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WebhookResponseManager
     */
    private $webhookResponseManager;

    public function __construct(
        OrderFactory $orderFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        LoggerInterface $logger,
        WebhookResponseManager $webhookResponseManager
    ) {
        $this->orderFactory = $orderFactory;
        $this->historyRepository = $historyRepository;
        $this->logger = $logger;
        $this->webhookResponseManager = $webhookResponseManager;
    }

    public function addComment(WebhooksEvent $webhookEvent): void
    {
        try {
            $response = $this->webhookResponseManager->getResponse($webhookEvent);
            $order = $this->getOrder($response);
            if (!$order->getId()) {
                return;
            }

            $comment = $order->addCommentToStatusHistory(
                'Webhook: ' . ' payment id (' . $response->getId() . '), '
                . 'type (' . $webhookEvent->type . '), '
                . 'status code (' . $response->getStatusOutput()->getStatusCode() . ')'
            );

            $this->historyRepository->save($comment);
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * @param DataObject $response
     * @return Order
     * @throws LocalizedException
     */
    private function getOrder(DataObject $response): Order
    {
        $incrementId = null;
        if ($response instanceof PaymentResponse) {
            $incrementId = (string) $response->getPaymentOutput()->getReferences()->getMerchantReference();
        } elseif ($response instanceof RefundResponse) {
            $incrementId = (string) $response->getRefundOutput()->getReferences()->getMerchantReference();
        }

        if (!$incrementId) {
            throw new LocalizedException(__('Increment id is missing'));
        }

        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }
}
