<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\PaymentOrderManager;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;

/**
 * Retrieve payment request
 */
class PaymentService
{
    /**
     * @var GetPaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    public function __construct(
        GetPaymentServiceInterface $paymentService,
        PaymentIdFormatterInterface $paymentIdFormatter
    ) {
        $this->paymentService = $paymentService;
        $this->paymentIdFormatter = $paymentIdFormatter;
    }

    public function getPaymentResponse(PaymentInterface $payment): ?PaymentResponse
    {
        $wlPaymentId = $this->paymentIdFormatter->validateAndFormat(
            (string) $payment->getAdditionalInformation('payment_id'),
            true
        );

        $storeId = (int)$payment->getMethodInstance()->getStore();

        try {
            return $this->paymentService->execute($wlPaymentId, $storeId);
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
