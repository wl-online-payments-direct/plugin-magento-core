<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\OrderReferences;
use OnlinePayments\Sdk\Domain\OrderReferencesFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\ReferenceDataBuilderInterface;

class ReferenceDataBuilder implements ReferenceDataBuilderInterface
{
    /**
     * @var OrderReferencesFactory
     */
    private $orderReferencesFactory;

    public function __construct(
        OrderReferencesFactory $orderReferencesFactory
    ) {
        $this->orderReferencesFactory = $orderReferencesFactory;
    }

    public function build(CartInterface $quote): OrderReferences
    {
        /** @var OrderReferences $references */
        $references = $this->orderReferencesFactory->create();
        $references->setMerchantReference($quote->getReservedOrderId());

        return $references;
    }
}
