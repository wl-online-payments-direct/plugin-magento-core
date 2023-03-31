<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\Creation;

use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Model\OutStockRefunder;
use Worldline\PaymentCore\Model\ResourceModel\PendingOrderProvider;
use Worldline\PaymentCore\Model\Order\FailedOrderCreationNotification;

class OrderCreationProcessor
{
    /**
     * @var PendingOrderProvider
     */
    private $quoteProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PendingOrderManagerInterface
     */
    private $pendingOrderManager;

    /**
     * @var FailedOrderCreationNotification
     */
    private $failedOrderCreationNotification;

    /**
     * @var OutStockRefunder
     */
    private $outStockRefunder;

    public function __construct(
        PendingOrderProvider $quoteProvider,
        LoggerInterface $logger,
        PendingOrderManagerInterface $pendingOrderManager,
        FailedOrderCreationNotification $failedOrderCreationNotification,
        OutStockRefunder $outStockRefunder
    ) {
        $this->quoteProvider = $quoteProvider;
        $this->logger = $logger;
        $this->pendingOrderManager = $pendingOrderManager;
        $this->failedOrderCreationNotification = $failedOrderCreationNotification;
        $this->outStockRefunder = $outStockRefunder;
    }

    public function process(?string $incrementOrderId = null): void
    {
        if ($incrementOrderId) {
            $this->placeOrder($incrementOrderId);
            return;
        }

        foreach ($this->quoteProvider->getReservedOrderIds() as $reservedOrderId) {
            $this->placeOrder((string) $reservedOrderId);
        }
    }

    private function placeOrder(string $reservedOrderId): void
    {
        try {
            $this->pendingOrderManager->processPendingOrder($reservedOrderId);
        } catch (\Exception $exception) {
            $this->failedOrderCreationNotification->notify(
                $reservedOrderId,
                (string)$exception->getMessage(),
                FailedOrderCreationNotification::WAITING_CRON_SPACE
            );
            $this->outStockRefunder->refundTransaction($reservedOrderId);
            $this->logger->warning($exception->getMessage(), ['reserved_order_id' => $reservedOrderId]);
        }
    }
}
