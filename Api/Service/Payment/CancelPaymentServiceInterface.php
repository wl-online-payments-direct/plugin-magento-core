<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;

interface CancelPaymentServiceInterface
{
    /**
     * Cancel payment by payment id
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return CancelPaymentResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): CancelPaymentResponse;
}
