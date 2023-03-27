<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
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

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TransactionCollectionFactory $transactionCollectionFactory,
        LoggerInterface $logger,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->amountFormatter = $amountFormatter;
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
            $additionalData = $item->getData('additional_data');
            $paymentDetails[] = [
                PaymentInterface::INCREMENT_ID => $item->getIncrementId(),
                PaymentInterface::PAYMENT_ID => $item->getTransactionId(),
                PaymentInterface::PAYMENT_PRODUCT_ID =>
                    $additionalData[PaymentInterface::PAYMENT_PRODUCT_ID] ?? 0,
                PaymentInterface::AMOUNT =>
                    $this->amountFormatter->formatToInteger((float) $item->getAmount(), (string) $item->getCurrency()),
                PaymentInterface::CURRENCY => $item->getCurrency(),
                'fraud_result' => $additionalData['fraud_result'] ?? '',
                'card_number' => $additionalData['card_number'] ?? '',
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
