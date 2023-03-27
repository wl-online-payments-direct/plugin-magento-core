<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use OnlinePayments\Sdk\Domain\SurchargeSpecificInput;
use OnlinePayments\Sdk\Domain\SurchargeSpecificInputFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\SurchargeDataBuilderInterface;

class SurchargeDataBuilder implements SurchargeDataBuilderInterface
{
    public const SURCHARGE_MODE = 'on-behalf-of';

    /**
     * @var SurchargeSpecificInputFactory
     */
    private $surchargeSIFactory;

    public function __construct(SurchargeSpecificInputFactory $surchargeSIFactory)
    {
        $this->surchargeSIFactory = $surchargeSIFactory;
    }

    public function build(): SurchargeSpecificInput
    {
        $surchargeSpecInput = $this->surchargeSIFactory->create();

        $surchargeSpecInput->setMode(self::SURCHARGE_MODE);

        return $surchargeSpecInput;
    }
}
