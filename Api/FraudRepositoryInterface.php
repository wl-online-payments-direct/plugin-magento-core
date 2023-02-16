<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\FraudInterface;

/**
 * Repository interface for fraud entity
 */
interface FraudRepositoryInterface
{
    public function save(FraudInterface $fraudEntity): FraudInterface;

    public function getByIncrementId(string $incrementId): FraudInterface;
}
