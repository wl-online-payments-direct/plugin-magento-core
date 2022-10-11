<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;

interface TransactionWLResponseManagerInterface
{
    /**
     * @param DataObject $worldlineResponse (PaymentResponse|RefundResponse)
     * @return void
     */
    public function saveTransaction(DataObject $worldlineResponse): void;
}
