<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\FraudInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Model\Fraud\ResourceModel\Fraud as FraudResource;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment\CollectionFactory as PaymentCollectionFactory;

/**
 * Copy fraud information from worldline_payment to worldline_fraud_information
 */
class ExtractFraudDataIntoTable implements DataPatchInterface
{
    /**
     * @var PaymentCollectionFactory
     */
    private $paymentCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PaymentCollectionFactory $paymentCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): ExtractFraudDataIntoTable
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $collection = $this->paymentCollectionFactory->create();

        $fraudInformation = [];
        /** @var TransactionInterface $item */
        foreach ($collection->getItems() as $item) {
            $fraudInformation[] = [
                FraudInterface::WORLDLINE_PAYMENT_ID => $item->getEntityId(),
                FraudInterface::RESULT => $item->getFraudResult(),
            ];
        }

        if (!$fraudInformation) {
            $connection->endSetup();
            return $this;
        }

        try {
            $connection->insertMultiple($this->moduleDataSetup->getTable(FraudResource::TABLE_NAME), $fraudInformation);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }

        $connection->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
