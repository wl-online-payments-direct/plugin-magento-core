<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup;

use Magento\Framework\FlagManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Worldline\PaymentCore\Cron\OrderCreator;

/**
 * Reset worldline order update watcher flag
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(FlagManager $flagManager)
    {
        $this->flagManager = $flagManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $flagData = $this->flagManager->getFlagData(OrderCreator::ORDER_WATCHER_FLAG);
        if ($flagData) {
            $this->flagManager->saveFlag(OrderCreator::ORDER_WATCHER_FLAG, false);
        }

        $setup->endSetup();
    }
}
