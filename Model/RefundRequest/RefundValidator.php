<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Sales\Model\Order;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;

class RefundValidator
{
    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var bool|null
     */
    private $result;

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    public function __construct(
        RefundRequestRepositoryInterface $refundRequestRepository,
        AmountFormatterInterface $amountFormatter
    ) {
        $this->refundRequestRepository = $refundRequestRepository;
        $this->amountFormatter = $amountFormatter;
    }

    public function canRefund(Order $order): bool
    {
        if ($this->result === null) {
            $this->result = $this->canRefundResult($order);
        }

        return $this->result;
    }

    private function canRefundResult(Order $order): bool
    {
        $incrementId = $order->getIncrementId();
        $refundRequests  = $this->refundRequestRepository->getListByIncrementId($incrementId);
        if (!$refundRequests) {
            return true;
        }

        $refundAmount = 0;
        foreach ($refundRequests as $refundRequest) {
            $refundAmount += $refundRequest->getAmount();
        }

        $orderAmount = $this->amountFormatter->formatToInteger(
            (float) $order->getGrandTotal(),
            (string) $order->getOrderCurrencyCode()
        );
        return $orderAmount !== $refundAmount;
    }
}
