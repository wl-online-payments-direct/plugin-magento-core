<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use OnlinePayments\Sdk\Domain\DataObject;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;

class TransactionUpdater
{
    /**
     * @var TransactionResource
     */
    private $transactionResource;

    public function __construct(
        TransactionResource $transactionResource
    ) {
        $this->transactionResource = $transactionResource;
    }

    public function update(DataObject $worldlineResponse): void
    {
        $operations = $worldlineResponse->getOperations();
        if (!$operations) {
            return;
        }

        $incrementId = (string) $worldlineResponse->getPaymentOutput()->getReferences()->getMerchantReference();
        $transactions = [];
        foreach ($operations as $operation) {
            $transactions[] = [
                TransactionInterface::INCREMENT_ID => $incrementId,
                TransactionInterface::STATUS => $operation->getStatus(),
                TransactionInterface::STATUS_CODE => $operation->getStatusOutput()->getStatusCode(),
                TransactionInterface::AMOUNT => $operation->getAmountOfMoney()->getAmount(),
                TransactionInterface::CURRENCY => $operation->getAmountOfMoney()->getCurrencyCode(),
                TransactionInterface::TRANSACTION_ID => $operation->getId(),
            ];
        }

        if (!$transactions) {
            return;
        }

        $this->transactionResource->removeByIncrementId($incrementId);
        $this->transactionResource->insertMultipleTransactions($transactions);
    }
}
