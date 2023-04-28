<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog;
use Worldline\PaymentCore\Model\Fraud\ResourceModel\Fraud;
use Worldline\PaymentCore\Model\Log\ResourceModel\Log;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment;
use Worldline\PaymentCore\Model\RefundRequest\ResourceModel\RefundRequest;
use Worldline\PaymentCore\Model\ResourceModel\FailedPaymentLog;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook;

class Uninstall implements UninstallInterface
{
    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;

    public function __construct(ConfigResource $configResource, CollectionFactory $configCollectionFactory)
    {
        $this->configResource = $configResource;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $setup->getConnection()->dropTable($setup->getTable(Log::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(Fraud::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(Payment::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(RequestLog::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(FailedPaymentLog::TABLE));
        $setup->getConnection()->dropTable($setup->getTable(Transaction::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(RefundRequest::TABLE_NAME));
        $setup->getConnection()->dropTable($setup->getTable(Webhook::TABLE_NAME));
        $this->clearConfigurations();

        $setup->endSetup();
    }

    private function clearConfigurations(): void
    {
        $collection = $this->configCollectionFactory->create()
            ->addFieldToFilter(
                'path',
                [
                    ['like' => 'worldline_connection/connection/%'],
                    ['like' => 'worldline_connection/webhook/%'],
                    ['like' => 'worldline_order_creator/general/%'],
                    ['like' => 'worldline_debug/general/%']
                ]
            );

        foreach ($collection->getItems() as $config) {
            $this->configResource->delete($config);
        }
    }
}
