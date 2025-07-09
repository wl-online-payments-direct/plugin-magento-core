<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\DataObject;
use Worldline\PaymentCore\Api\Data\FraudInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;

/**
 * Manager interface for fraud entity
 */
interface FraudManagerInterface
{
    public function saveFraudInformation(DataObject $worldlineResponse, PaymentInterface $wlPayment): ?FraudInterface;
}
