<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\PaymentOrderManager;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;

/**
 * Retrieve payment request
 */
class PaymentService
{
    /**
     * @var GetPaymentDetailsServiceInterface
     */
    private $paymentDetailsService;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    public function __construct(
        GetPaymentDetailsServiceInterface $paymentDetailsService,
        PaymentIdFormatterInterface $paymentIdFormatter
    ) {
        $this->paymentDetailsService = $paymentDetailsService;
        $this->paymentIdFormatter = $paymentIdFormatter;
    }

    public function getPaymentResponse(PaymentInterface $payment): ?PaymentDetailsResponse
    {
        $wlPaymentId = $this->paymentIdFormatter->validateAndFormat(
            (string) $payment->getAdditionalInformation('payment_id'),
            true
        );

        $storeId = (int)$payment->getMethodInstance()->getStore();

        try {
            return $this->paymentDetailsService->execute($wlPaymentId, $storeId);
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
