<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Services;

interface TestConnectionServiceInterface
{
    public function execute(): string;
}
