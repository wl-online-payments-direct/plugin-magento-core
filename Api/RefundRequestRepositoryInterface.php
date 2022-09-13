<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\RefundRequestInterface;

interface RefundRequestRepositoryInterface
{
    public function getListByIncrementId(string $incrementId): array;
    public function getByIncrementIdAndAmount(string $incrementId, int $amount): RefundRequestInterface;
    public function save(RefundRequestInterface $refundRequest): RefundRequestInterface;
}
