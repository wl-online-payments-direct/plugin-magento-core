<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\CreditmemoOnlineServiceInterface;
use Worldline\PaymentCore\Api\Data\RefundRequestInterfaceFactory;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\Service\Refund\CreateRefundServiceInterface;
use Worldline\PaymentCore\Api\Service\Refund\RefundRequestDataBuilderInterface;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;

class CreditmemoOnlineService implements CreditmemoOnlineServiceInterface
{
    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var RefundRequestInterfaceFactory
     */
    private $refundRequestFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var CreateRefundServiceInterface
     */
    private $createRefundService;

    /**
     * @var RefundRequestDataBuilderInterface
     */
    private $refundRequestDataBuilder;

    public function __construct(
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundRequestInterfaceFactory $refundRequestFactory,
        OrderRepositoryInterface $orderRepository,
        AmountFormatterInterface $amountFormatter,
        TransactionRepositoryInterface $transactionRepository,
        CreateRefundServiceInterface $createRefundService,
        RefundRequestDataBuilderInterface $refundRequestDataBuilder
    ) {
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->orderRepository = $orderRepository;
        $this->amountFormatter = $amountFormatter;
        $this->transactionRepository = $transactionRepository;
        $this->createRefundService = $createRefundService;
        $this->refundRequestDataBuilder = $refundRequestDataBuilder;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @return CreditmemoInterface
     * @throws LocalizedException
     */
    public function refund(CreditmemoInterface $creditmemo): CreditmemoInterface
    {
        $order = $creditmemo->getOrder();
        $invoice = $creditmemo->getInvoice();
        if (!$invoice) {
            return $creditmemo;
        }

        $invoiceId = (int)$invoice->getId();
        $payment = $order->getPayment();
        $incrementId = (string)$order->getIncrementId();
        $currencyCode = (string)$order->getOrderCurrencyCode();

        $baseAmountToRefund = (float)$creditmemo->getBaseGrandTotal();
        if ($this->isSplitPayment($incrementId)) {
            $this->refundSplitPayment($incrementId, $currencyCode, (int)$order->getStoreId(), $baseAmountToRefund);
        } else {
            $gateway = $payment->getMethodInstance();
            $gateway->setStore($order->getStoreId());
            $gateway->refund($payment, $payment->formatAmount($baseAmountToRefund));
        }

        $payment->addTransaction(TransactionInterface::TYPE_REFUND, $creditmemo, true);

        $creditmemo->setState(Creditmemo::STATE_OPEN);
        $this->creditmemoRepository->save($creditmemo);

        $amount = $this->amountFormatter->formatToInteger(
            (float) $creditmemo->getGrandTotal(),
            $currencyCode
        );
        $this->saveRefundRequest($invoiceId, $incrementId, (int)$creditmemo->getId(), $amount);
        $this->orderRepository->save($order);

        return $creditmemo;
    }

    private function isSplitPayment(string $incrementId): bool
    {
        return count($this->transactionRepository->getAllCapturedTransactions($incrementId)) > 1;
    }

    /**
     * Refund split payment: first refund the card (non-gift-card) transaction,
     * then refund the remainder from the gift card transaction.
     *
     * @throws LocalizedException
     */
    private function refundSplitPayment(
        string $incrementId,
        string $currencyCode,
        int $storeId,
        float $totalRefundAmount
    ): void {
        $capturedTransactions = $this->transactionRepository->getAllCapturedTransactions($incrementId);
        $amountInCents = $this->amountFormatter->formatToInteger($totalRefundAmount, $currencyCode);
        $remaining = $amountInCents;

        // Sort: card transactions first (higher amounts typically), gift card last
        usort($capturedTransactions, function ($a, $b) {
            return $b->getAmount() <=> $a->getAmount();
        });

        foreach ($capturedTransactions as $transaction) {
            if ($remaining <= 0) {
                break;
            }

            $capturedAmount = (int)$transaction->getAmount();
            $refundForThis = min($remaining, $capturedAmount);
            $remaining -= $refundForThis;

            $refundAmount = $refundForThis / 100;
            $refundRequest = $this->refundRequestDataBuilder->build($refundAmount, $currencyCode);
            $this->createRefundService->execute($transaction->getTransactionId(), $refundRequest, $storeId);
        }
    }

    private function saveRefundRequest(
        int $invoiceId,
        string $incrementId,
        int $creditMemoId,
        int $amount
    ): void {
        $refundRequest = $this->refundRequestFactory->create();

        $refundRequest->setInvoiceId($invoiceId);
        $refundRequest->setIncrementId($incrementId);
        $refundRequest->setCreditMemoId($creditMemoId);
        $refundRequest->setAmount($amount);

        $this->refundRequestRepository->save($refundRequest);
    }
}
