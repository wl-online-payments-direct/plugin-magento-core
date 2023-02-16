<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service;

use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use Magento\Framework\Exception\LocalizedException;

/**
 * Implementation for GetPaymentDetailsApi
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/GetPaymentDetailsApi
 */
interface GetPaymentDetailsServiceInterface
{
    /**
     * Retrieve payment detail data
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return PaymentDetailsResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): PaymentDetailsResponse;
}
