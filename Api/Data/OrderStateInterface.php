<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

use Magento\Framework\Phrase;

interface OrderStateInterface
{
    public function getState(): string;

    public function setState(string $state): void;

    public function getIncrementId(): string;

    public function setIncrementId(string $incrementId): void;

    /**
     * @return Phrase|string
     */
    public function getMessage();

    /**
     * @param Phrase|string $message
     * @return void
     */
    public function setMessage($message): void;
}
