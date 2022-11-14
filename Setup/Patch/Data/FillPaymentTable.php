<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment as PaymentResource;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction\Collection as TransactionCollection;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;

class FillPaymentTable implements DataPatchInterface
{
    /**
     * @var TransactionCollectionFactory
     */
    private $transactionCollectionFactory;

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
        TransactionCollectionFactory $transactionCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): FillPaymentTable
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        /** @var TransactionCollection $collection */
        $collection = $this->transactionCollectionFactory->create();
        $collection->getSelect()->where('additional_data IS NOT NULL');
        $collection->addFieldToFilter('transaction_id', ['like' => '%_0']);

        $paymentDetails = [];
        /** @var TransactionInterface $item */
        foreach ($collection->getItems() as $item) {
            $paymentDetails[] = [
                PaymentInterface::INCREMENT_ID => $item->getIncrementId(),
                PaymentInterface::PAYMENT_ID => $item->getTransactionId(),
                PaymentInterface::PAYMENT_PRODUCT_ID =>
                    $item->getAdditionalData()[PaymentInterface::PAYMENT_PRODUCT_ID] ?? 0,
                PaymentInterface::AMOUNT => round($item->getAmount() * 100),
                PaymentInterface::CURRENCY => $item->getCurrency(),
                PaymentInterface::FRAUD_RESULT => $item->getAdditionalData()[PaymentInterface::FRAUD_RESULT] ?? '',
                PaymentInterface::CARD_NUMBER => $item->getAdditionalData()[PaymentInterface::CARD_NUMBER] ?? '',
            ];
        }

        if (!$paymentDetails) {
            $connection->endSetup();
            return $this;
        }

        try {
            $connection->insertMultiple($this->moduleDataSetup->getTable(PaymentResource::TABLE_NAME), $paymentDetails);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }

        $connection->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [
            UpdateTransactionTable::class,
            ClearDuplicatesTransactionTable::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
