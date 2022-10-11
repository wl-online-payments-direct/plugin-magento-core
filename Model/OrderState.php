<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\Phrase;
use Worldline\PaymentCore\Api\Data\OrderStateInterface;

class OrderState implements OrderStateInterface
{
    /**
     * @var string
     */
    private $state = '';

    /**
     * @var string
     */
    private $incrementId = '';

    /**
     * @var Phrase|string
     */
    private $message = '';

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getIncrementId(): string
    {
        return $this->incrementId;
    }

    public function setIncrementId(string $incrementId): void
    {
        $this->incrementId = $incrementId;
    }

    /**
     * @return Phrase|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Phrase|string $message
     * @return void
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }
}
