<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Model\ResourceModel\FailedPaymentLog;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

class RefusedStatusProcessor
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var FailedPaymentLog
     */
    private $failedPaymentLog;

    public function __construct(
        EmailSender $emailSender,
        FailedPaymentLog $failedPaymentLog
    ) {
        $this->emailSender = $emailSender;
        $this->failedPaymentLog = $failedPaymentLog;
    }

    public function process(CartInterface $quote, ?int $statusCode): void
    {
        $isPaymentRefused = in_array(
            $statusCode,
            [
                TransactionStatusInterface::AUTHORISATION_DECLINED,
                TransactionStatusInterface::AUTHORISED_AND_CANCELLED,
                TransactionStatusInterface::PAYMENT_REFUSED
            ],
            true
        );

        if ($isPaymentRefused) {
            $this->emailSender->sendPaymentRefusedEmail($quote);
        } else {
            $this->failedPaymentLog->saveQuotePaymentId((int) $quote->getPayment()->getId());
        }
    }
}
