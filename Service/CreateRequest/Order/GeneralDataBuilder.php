<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\GeneralDataBuilderInterface;

class GeneralDataBuilder implements GeneralDataBuilderInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var AmountDataBuilder
     */
    private $amountDataBuilder;

    /**
     * @var CustomerDataBuilder
     */
    private $customerDataBuilder;

    /**
     * @var ReferenceDataBuilder
     */
    private $referenceDataBuilder;

    /**
     * @var ShippingAddressDataBuilder
     */
    private $shippingAddressDataBuilder;

    public function __construct(
        OrderFactory $orderFactory,
        AmountDataBuilder $amountDataBuilder,
        CustomerDataBuilder $customerDataBuilder,
        ReferenceDataBuilder $referenceDataBuilder,
        ShippingAddressDataBuilder $shippingAddressDataBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->amountDataBuilder = $amountDataBuilder;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->referenceDataBuilder = $referenceDataBuilder;
        $this->shippingAddressDataBuilder = $shippingAddressDataBuilder;
    }

    public function build(CartInterface $quote): Order
    {
        $order = $this->orderFactory->create();

        $order->setAmountOfMoney($this->amountDataBuilder->build($quote));
        $order->setCustomer($this->customerDataBuilder->build($quote));
        $order->setReferences($this->referenceDataBuilder->build($quote));
        $order->setShipping($this->shippingAddressDataBuilder->build($quote));

        return $order;
    }
}
