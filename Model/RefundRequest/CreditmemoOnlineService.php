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

    public function __construct(
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundRequestInterfaceFactory $refundRequestFactory,
        OrderRepositoryInterface $orderRepository,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->orderRepository = $orderRepository;
        $this->amountFormatter = $amountFormatter;
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
        $baseAmountToRefund = $payment->formatAmount($creditmemo->getBaseGrandTotal());
        $gateway = $payment->getMethodInstance();
        $gateway->setStore($order->getStoreId());
        $gateway->refund($payment, $baseAmountToRefund);

        $payment->addTransaction(TransactionInterface::TYPE_REFUND, $creditmemo, true);

        $creditmemo->setState(Creditmemo::STATE_OPEN);
        $this->creditmemoRepository->save($creditmemo);

        $amount = $this->amountFormatter->formatToInteger(
            (float) $creditmemo->getGrandTotal(),
            (string) $creditmemo->getOrderCurrencyCode()
        );
        $this->saveRefundRequest($invoiceId, $order->getIncrementId(), (int)$creditmemo->getId(), $amount);
        $this->orderRepository->save($order); //need to save $order->getCustomerNoteNotify() flag changes

        return $creditmemo;
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
