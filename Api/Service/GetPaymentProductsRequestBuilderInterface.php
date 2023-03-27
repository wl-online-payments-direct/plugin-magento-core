<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service;

use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;

interface GetPaymentProductsRequestBuilderInterface
{
    public function build(?int $storeId = null): GetPaymentProductsParams;
}
