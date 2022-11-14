<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\DataObject;

interface PaymentManagerInterface
{
    public function savePayment(DataObject $worldlineResponse): void;
}
