<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\CreateRequest\Order;

use OnlinePayments\Sdk\Domain\SurchargeSpecificInput;

interface SurchargeDataBuilderInterface
{
    public function build(): SurchargeSpecificInput;
}
