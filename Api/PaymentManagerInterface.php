<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\DataObject;
use Worldline\PaymentCore\Api\Data\PaymentInterface;

/**
 * Manager interface for worldline payment entity
 */
interface PaymentManagerInterface
{
    public function savePayment(DataObject $worldlineResponse): PaymentInterface;

    public function updatePayment(DataObject $worldlineResponse): PaymentInterface;
}
