<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;

interface GetPaymentServiceInterface
{
    /**
     * Get payment information
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return PaymentResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): PaymentResponse;
}
