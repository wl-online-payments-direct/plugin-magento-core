<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Token;

interface DeleteTokenServiceInterface
{
    /**
     * @param string $token
     * @param int|null $storeId
     * @return void
     */
    public function execute(string $token, ?int $storeId = null): void;
}
