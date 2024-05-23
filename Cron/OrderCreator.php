<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Cron;

use Magento\Framework\FlagManager;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Order\Creation\OrderCreationProcessor;

class OrderCreator
{
    public const FIFTEEN_MINUTES_IN_SEC = 900;
    public const ORDER_WATCHER_FLAG = 'worldline_order_update_watcher';

    /**
     * @var OrderCreationProcessor
     */
    private $orderCreationProcessor;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        OrderCreationProcessor $orderCreationProcessor,
        FlagManager $flagManager,
        LoggerInterface $logger
    ) {
        $this->orderCreationProcessor = $orderCreationProcessor;
        $this->flagManager = $flagManager;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $flagData = $this->flagManager->getFlagData(self::ORDER_WATCHER_FLAG);
        if (!empty($flagData['timestamp'])) {
            if (time() < $flagData['timestamp'] + self::FIFTEEN_MINUTES_IN_SEC) {
                return;
            }
        }

        try {
            $this->flagManager->saveFlag(self::ORDER_WATCHER_FLAG, ['state' => true, 'timestamp' => time()]);
            $this->orderCreationProcessor->process();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            $this->flagManager->saveFlag(self::ORDER_WATCHER_FLAG, false);
        }
    }
}
