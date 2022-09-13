<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\LogInterface;

interface LogRepositoryInterface
{
    /**
     * @param int $logId
     * @return LogInterface
     */
    public function getById(int $logId): LogInterface;

    /**
     * @param int $logId
     * @return bool
     */
    public function delete(int $logId): bool;

    /**
     * @param LogInterface $log
     * @return LogInterface
     */
    public function save(LogInterface $log): LogInterface;
}
