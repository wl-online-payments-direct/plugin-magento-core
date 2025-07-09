<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;

interface CancelPaymentServiceInterface
{
    /**
     * Cancel payment by payment id
     *
     * @param PaymentResponse $payment
     * @param int|null $storeId
     * @return CancelPaymentResponse
     * @throws LocalizedException
     */
    public function execute(PaymentResponse $payment, ?int $storeId = null): CancelPaymentResponse;
}
