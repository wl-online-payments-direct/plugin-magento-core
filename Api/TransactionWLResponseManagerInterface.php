<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;

interface TransactionWLResponseManagerInterface
{
    /**
     * @param DataObject $worldlineResponse (PaymentResponse|RefundResponse)
     * @return void
     * @throws LocalizedException
     */
    public function saveTransaction(DataObject $worldlineResponse): void;
}
