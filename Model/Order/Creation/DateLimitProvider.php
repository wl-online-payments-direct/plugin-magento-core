<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\Creation;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Worldline\PaymentCore\Model\Config\OrderSynchronizationConfig;

class DateLimitProvider
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var OrderSynchronizationConfig
     */
    private $orderSynchronizationConfig;

    public function __construct(
        DateTime $dateTime,
        OrderSynchronizationConfig $orderSynchronizationConfig
    ) {
        $this->dateTime = $dateTime;
        $this->orderSynchronizationConfig = $orderSynchronizationConfig;
    }

    public function getDateFrom(): ?string
    {
        if (!$this->orderSynchronizationConfig->getFallbackTimeout()) {
            return null;
        }

        $timestampNow = $this->dateTime->gmtTimestamp();
        return date(
            'Y-m-d H:i:s',
            $timestampNow - $this->orderSynchronizationConfig->getFallbackTimeout() * 60
        );
    }

    public function getDateTo(): ?string
    {
        if (!$this->orderSynchronizationConfig->getFallbackTimeoutLimit()) {
            return null;
        }

        $timestampNow = $this->dateTime->gmtTimestamp();
        return date(
            'Y-m-d H:i:s',
            $timestampNow - $this->orderSynchronizationConfig->getFallbackTimeoutLimit() * 60
        );
    }
}
