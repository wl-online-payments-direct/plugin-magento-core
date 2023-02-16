<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use OnlinePayments\Sdk\Domain\CaptureResponse;

interface CapturePaymentServiceInterface
{
    /**
     * @param string $transactionId
     * @param CapturePaymentRequest $capturePaymentRequest
     * @param int|null $storeId
     * @return CaptureResponse
     * @throws LocalizedException
     */
    public function execute(
        string $transactionId,
        CapturePaymentRequest $capturePaymentRequest,
        ?int $storeId = null
    ): CaptureResponse;
}
