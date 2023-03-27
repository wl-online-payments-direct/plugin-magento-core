<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Client;

interface ClientProviderInterface
{
    public function getClient(?int $storeId = null): Client;
}
