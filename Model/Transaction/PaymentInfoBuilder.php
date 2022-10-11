<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Sales\Api\Data\OrderInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterfaceFactory;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;

class PaymentInfoBuilder
{
    /**
     * @var PaymentInfoInterfaceFactory
     */
    private $paymentInfoFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        PaymentInfoInterfaceFactory $paymentInfoFactory,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->transactionRepository = $transactionRepository;
    }

    public function build(OrderInterface $order): PaymentInfoInterface
    {
        /** @var PaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();

        $incrementId = (string)$order->getIncrementId();
        $lastTransaction = $this->transactionRepository->getLastTransaction($incrementId);
        $paymentInfo = $this->setStatusInfo($paymentInfo, $lastTransaction);

        $authorizeTransaction = $this->transactionRepository->getAuthorizeTransaction($incrementId);

        $paymentInfo = $this->setInfoByAuthorizeTransaction($paymentInfo, $authorizeTransaction);
        $paymentInfo = $this->calculateTransactionAmounts($paymentInfo, $authorizeTransaction, $incrementId);

        return $paymentInfo;
    }

    private function setInfoByAuthorizeTransaction(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $authorizeTransaction
    ): PaymentInfoInterface {
        if (!$authorizeTransaction) {
            return $paymentInfo;
        }

        $paymentInfo->setAuthorizedAmount($authorizeTransaction->getAmount());
        $paymentInfo->setFraudResult(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::FRAUD_RESULT] ?? ''
        );
        $paymentInfo->setPaymentMethod(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::PAYMENT_METHOD] ?? ''
        );
        $paymentInfo->setCardLastNumbers(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::CARD_LAST_4] ?? ''
        );
        $paymentInfo->setPaymentProductId(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::PAYMENT_PRODUCT_ID] ?? 0
        );
        $paymentInfo->setCurrency($authorizeTransaction->getCurrency());

        return $paymentInfo;
    }

    private function calculateTransactionAmounts(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $authorizeTransaction,
        string $incrementId
    ): PaymentInfoInterface {
        $authorizeAmount = $authorizeTransaction ? $authorizeTransaction->getAmount() : 0;
        $captureAmount = $this->transactionRepository->getCaptureTransactionsAmount($incrementId);
        if ($authorizeAmount) {
            $paymentInfo->setAmountAvailableForCapture($authorizeAmount - $captureAmount);
        }

        $refundAmount = $this->transactionRepository->getRefundedTransactionsAmount($incrementId);
        $paymentInfo->setRefundedAmount($refundAmount);

        if ($captureAmount) {
            $pendingRefundAmount = $this->transactionRepository->getPendingRefundTransactionsAmount($incrementId);
            $amountAvailableForRefund = $captureAmount - $pendingRefundAmount - $refundAmount;
            $paymentInfo->setAmountAvailableForRefund($amountAvailableForRefund);

            if ($amountAvailableForRefund > 0 || $authorizeAmount > $captureAmount) {
                $captureTransaction = $this->transactionRepository->getCaptureTransaction($incrementId);
                $paymentInfo = $this->setStatusInfo($paymentInfo, $captureTransaction);
            } elseif ($refundAmount) {
                $refundTransactions = $this->transactionRepository->getRefundedTransactions($incrementId);
                $paymentInfo = $this->setStatusInfo($paymentInfo, current($refundTransactions));
            }
        }

        return $paymentInfo;
    }

    private function setStatusInfo(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $transaction
    ): PaymentInfoInterface {
        if ($transaction) {
            $paymentInfo->setStatus($transaction->getStatus());
            $paymentInfo->setStatusCode($transaction->getStatusCode());
            $paymentInfo->setLastTransactionNumber($transaction->getTransactionId());
        }

        return $paymentInfo;
    }
}
