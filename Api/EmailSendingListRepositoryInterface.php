<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;

interface EmailSendingListRepositoryInterface
{
    public function count(string $incrementId, string $level): int;

    public function save(EmailSendingListInterface $emailSendingList): EmailSendingListInterface;

    public function setQuoteToEmailList(string $incrementId, string $level): void;
}
