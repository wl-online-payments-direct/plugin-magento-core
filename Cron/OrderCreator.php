<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Cron;

use Magento\Framework\FlagFactory;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Order\Creation\OrderCreationProcessor;

class OrderCreator
{
    /**
     * @var OrderCreationProcessor
     */
    private $orderCreationProcessor;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        OrderCreationProcessor $orderCreationProcessor,
        FlagFactory $flagFactory,
        LoggerInterface $logger
    ) {
        $this->orderCreationProcessor = $orderCreationProcessor;
        $this->flagFactory = $flagFactory;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $flagModel = $this->flagFactory->create(['data' =>  ['flag_code' => 'worldline_order_update_watcher']]);
        $flagModel->loadSelf();

        if ($flagModel->getFlagData()) {
            return;
        }

        try {
            $flagModel->setFlagData(true)->save();
            $this->orderCreationProcessor->process();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            $flagModel->setFlagData(false)->save();
        }
    }
}
