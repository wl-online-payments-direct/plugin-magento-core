<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Worldline\PaymentCore\Api\Data\TransactionInterfaceFactory;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;

class TransactionWLResponseManager implements TransactionWLResponseManagerInterface
{
    /**
     * @var TransactionInterfaceFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        TransactionInterfaceFactory $transactionFactory,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param DataObject $worldlineResponse (PaymentResponse|RefundResponse)
     * @return void
     * @throws LocalizedException
     */
    public function saveTransaction(DataObject $worldlineResponse): void
    {
        if (!$this->isValid($worldlineResponse)) {
            return;
        }

        $output = $this->getOutput($worldlineResponse);

        $transaction = $this->transactionFactory->create();
        $transaction->setIncrementId((string)$output->getReferences()->getMerchantReference());
        $transaction->setStatus((string)$worldlineResponse->getStatus());
        $transaction->setStatusCode((int)$worldlineResponse->getStatusOutput()->getStatusCode());
        $transaction->setTransactionId((string)$worldlineResponse->getId());
        $transaction->setAmount((int)$output->getAmountOfMoney()->getAmount());
        $transaction->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
        $this->transactionRepository->save($transaction);
    }

    /**
     * @param DataObject $response
     * @return DataObject
     * @throws LocalizedException
     */
    private function getOutput(DataObject $response): DataObject
    {
        $output = null;
        if ($response instanceof PaymentResponse) {
            $output = $response->getPaymentOutput();
        }

        if ($response instanceof RefundResponse) {
            $output = $response->getRefundOutput();
        }

        if (!$output) {
            throw new LocalizedException(__('Invalid output model'));
        }

        return $output;
    }

    private function isValid(DataObject $worldlineResponse): bool
    {
        $output = $this->getOutput($worldlineResponse);
        $incrementId = (string)$output->getReferences()->getMerchantReference();
        $statusCode = (int)$worldlineResponse->getStatusOutput()->getStatusCode();
        $transaction = $this->transactionRepository->getLastTransaction($incrementId);

        if (!$transaction) {
            return true;
        }

        if ((int)$transaction->getStatusCode() === $statusCode
            && (string) $transaction->getTransactionId() === (string)$worldlineResponse->getId()
        ) {
            return false;
        }

        return (int)$transaction->getTransactionId() === (int)$worldlineResponse->getId();
    }
}
