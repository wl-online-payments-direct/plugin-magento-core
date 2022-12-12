<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service;

use OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;

/**
 * Implementation for GetPaymentProducts
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Products/operation/GetPaymentProducts
 */
interface GetPaymentProductsServiceInterface
{
    /**
     * @param GetPaymentProductsParams $queryParams
     * @param int|null $storeId
     * @return GetPaymentProductsResponse
     */
    public function execute(GetPaymentProductsParams $queryParams, ?int $storeId = null): GetPaymentProductsResponse;
}
