<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Sales\Api\Data\OrderInterface;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterfaceFactory;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;

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

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    public function __construct(
        PaymentInfoInterfaceFactory $paymentInfoFactory,
        TransactionRepositoryInterface $transactionRepository,
        PaymentRepositoryInterface $paymentRepository,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->transactionRepository = $transactionRepository;
        $this->paymentRepository = $paymentRepository;
        $this->amountFormatter = $amountFormatter;
    }

    public function build(OrderInterface $order): PaymentInfoInterface
    {
        /** @var PaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();

        $incrementId = (string)$order->getIncrementId();
        $payment = $this->paymentRepository->get($incrementId);

        $lastTransaction = $this->transactionRepository->getLastTransaction($incrementId);
        $this->setStatusInfo($paymentInfo, $lastTransaction);
        $this->setPaymentInfo($paymentInfo, $payment);

        $this->calculateTransactionAmounts($paymentInfo, $payment, $incrementId);

        return $paymentInfo;
    }

    /**
     * @param PaymentResponse $paymentResponse
     * @param int $splitPaymentAmount
     *
     * @return PaymentInfoInterface
     */
    public function buildSplitTransaction(PaymentResponse $paymentResponse, int $splitPaymentAmount): PaymentInfoInterface
    {
        /** @var PaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();

        $paymentInfo->setStatus($paymentResponse->getStatus());
        $paymentInfo->setStatusCode($paymentResponse->getStatusOutput()->getStatusCode());
        $paymentInfo->setLastTransactionNumber($paymentResponse->getId());

        $paymentProductId = $paymentResponse->getPaymentOutput()->getRedirectPaymentMethodSpecificOutput()->getPaymentProductId();
        $currency = $paymentResponse->getPaymentOutput()->getAcquiredAmount()->getCurrencyCode();

        $paymentInfo->setAuthorizedAmount(
            $this->formatAmount(
                (int) $splitPaymentAmount,
                (string) $currency
            )
        );
        $paymentInfo->setFraudResult((string)$paymentResponse->getPaymentOutput()->
        getRedirectPaymentMethodSpecificOutput()->getFraudResults()->getFraudServiceResult());
        $paymentInfo->setCardLastNumbers('');
        $paymentInfo->setPaymentProductId((int) $paymentProductId);
        $paymentInfo->setCurrency((string) $currency);
        $paymentInfo->setPaymentMethod(
            PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$paymentProductId]['group'] ?? ''
        );

        return $paymentInfo;
    }

    /**
     * @param OrderInterface $order
     *
     * @return string|null
     */
    public function getPaymentByOrderId(OrderInterface $order): ?string
    {
        $incrementId = (string)$order->getIncrementId();
        $payment = $this->paymentRepository->get($incrementId);

        return $payment->getPaymentId();
    }

    /**
     * @param int $splitPaymentAmount
     * @param string $currency
     *
     * @return float
     */
    public function getFormattedSplitPaymentAmount(int $splitPaymentAmount, string $currency): float
    {
        return $this->formatAmount($splitPaymentAmount, $currency);
    }

    private function setPaymentInfo(
        PaymentInfoInterface $paymentInfo,
        PaymentInterface $payment
    ): void {
        $paymentInfo->setAuthorizedAmount(
            $this->formatAmount((int) $payment->getAmount(), (string) $payment->getCurrency())
        );
        $paymentInfo->setFraudResult((string)$payment->getFraudResult());
        $paymentInfo->setCardLastNumbers((string) $payment->getCardNumber());
        $paymentInfo->setPaymentProductId((int) $payment->getPaymentProductId());
        $paymentInfo->setCurrency((string)$payment->getCurrency());
        $paymentInfo->setPaymentMethod(
            PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$payment->getPaymentProductId()]['group'] ?? ''
        );
    }

    private function calculateTransactionAmounts(
        PaymentInfoInterface $paymentInfo,
        PaymentInterface $payment,
        string $incrementId
    ): void {
        $authorizedAmount = $payment->getAmount();
        $capturedAmount = $this->transactionRepository->getCaptureTransactionsAmount($incrementId);
        $amountAvailableForCapture = (int) round($authorizedAmount - $capturedAmount);
        $paymentInfo->setAmountAvailableForCapture(
            $this->formatAmount($amountAvailableForCapture, (string) $payment->getCurrency())
        );

        $refundAmount = (int) $this->transactionRepository->getRefundedTransactionsAmount($incrementId);
        $paymentInfo->setRefundedAmount($this->formatAmount($refundAmount, (string) $payment->getCurrency()));

        if (!$capturedAmount) {
            return;
        }

        $pendingRefundAmount = $this->transactionRepository->getPendingRefundTransactionsAmount($incrementId);
        $amountAvailableForRefund = (int) round($capturedAmount - $pendingRefundAmount - $refundAmount);
        $paymentInfo->setAmountAvailableForRefund(
            $this->formatAmount($amountAvailableForRefund, (string) $payment->getCurrency())
        );

        if ($amountAvailableForRefund > 0 || $authorizedAmount > $capturedAmount) {
            $captureTransaction = $this->transactionRepository->getCaptureTransaction($incrementId);
            $this->setStatusInfo($paymentInfo, $captureTransaction);
        } elseif ($refundAmount) {
            $refundTransactions = $this->transactionRepository->getRefundedTransactions($incrementId);
            $this->setStatusInfo($paymentInfo, current($refundTransactions));
        }
    }

    private function setStatusInfo(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $transaction
    ): void {
        if ($transaction) {
            $paymentInfo->setStatus($transaction->getStatus());
            $paymentInfo->setStatusCode($transaction->getStatusCode());
            $paymentInfo->setLastTransactionNumber($transaction->getTransactionId());
        }
    }

    private function formatAmount(int $amount, string $currency): float
    {
        return $this->amountFormatter->formatToFloat($amount, $currency);
    }
}
