<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml;

use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\InfoFormatterInterface;

/**
 * Order view payment info formatter for the admin panel
 */
class InfoFormatter implements InfoFormatterInterface
{
    public function format(PaymentInfoInterface $paymentInfo): array
    {
        $data = [
            'paymentProductId' => $paymentInfo->getPaymentProductId(),
            ['label' => __('Payment method'), 'value' => $paymentInfo->getPaymentMethod()],
            ['label' => __('Status'), 'value' => $paymentInfo->getStatus()],
            ['label' => __('Status code'), 'value' => $paymentInfo->getStatusCode()],
            ['label' => __('Transaction number'), 'value' => $paymentInfo->getLastTransactionNumber()],
            [
                'label' => __('Total'),
                'value' => $paymentInfo->getAuthorizedAmount() . ' ' . $paymentInfo->getCurrency()
            ],
        ];

        if ($paymentInfo->getAmountAvailableForCapture()) {
            $data[] = [
                'label' => __('Amount available for capture'),
                'value' => $paymentInfo->getAmountAvailableForCapture() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        if ($paymentInfo->getRefundedAmount()) {
            $data[] = [
                'label' => __('Refunded amount'),
                'value' => $paymentInfo->getRefundedAmount() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        if ($paymentInfo->getAmountAvailableForRefund()) {
            $data[] = [
                'label' => __('Amount available for refund'),
                'value' => $paymentInfo->getAmountAvailableForRefund() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        return $data;
    }
}
