<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsRequestInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;

class TransactionUpdater
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var GetPaymentDetailsRequestInterface
     */
    private $detailsRequest;

    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        GetPaymentDetailsRequestInterface $detailsRequest,
        TransactionResource $transactionResource,
        LoggerInterface $logger
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->detailsRequest = $detailsRequest;
        $this->transactionResource = $transactionResource;
        $this->logger = $logger;
    }

    /**
     * Update payment details
     *
     * @param string $incrementId
     * @param int|null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function updateForIncrementId(string $incrementId, ?int $storeId = null): bool
    {
        try {
            $wlPayment = $this->paymentRepository->get($incrementId);
            $response = $this->detailsRequest->get((string) $wlPayment->getPaymentId(), $storeId);
            $operations = $response->getOperations();
            if (!$operations) {
                return false;
            }

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
                return false;
            }

            $this->transactionResource->removeByIncrementId($incrementId);
            $this->transactionResource->insertMultipleTransactions($transactions);
            return true;
        } catch (LocalizedException $e) {
            $this->logger->warning($e->getMessage());
            throw new LocalizedException(__('Payment details update has failed'));
        }
    }
}
