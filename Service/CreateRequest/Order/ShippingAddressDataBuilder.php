<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AddressPersonalFactory;
use OnlinePayments\Sdk\Domain\PersonalNameFactory;
use OnlinePayments\Sdk\Domain\Shipping;
use OnlinePayments\Sdk\Domain\ShippingFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\ShippingAddressDataBuilderInterface;
use Worldline\PaymentCore\Model\Quote\Address\Comparer;

class ShippingAddressDataBuilder implements ShippingAddressDataBuilderInterface
{
    public const SAME_AS_SHIPPING = 'same-as-billing';
    public const DIFFERENT_THAN_BILLING = 'different-than-billing';

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var AddressPersonalFactory
     */
    private $addressPersonalFactory;

    /**
     * @var PersonalNameFactory
     */
    private $personalNameFactory;

    /**
     * @var Comparer
     */
    private $addressComparer;

    public function __construct(
        ShippingFactory $shippingFactory,
        AddressPersonalFactory $addressPersonalFactory,
        PersonalNameFactory $personalNameFactory,
        Comparer $addressComparer
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->addressPersonalFactory = $addressPersonalFactory;
        $this->personalNameFactory = $personalNameFactory;
        $this->addressComparer = $addressComparer;
    }

    public function build(CartInterface $quote): Shipping
    {
        $shippingAddress = $quote->getShippingAddress();
        $shipping = $this->shippingFactory->create();

        if (!$shippingAddress) {
            return $shipping;
        }

        $name = $this->personalNameFactory->create();
        $name->setFirstName($shippingAddress->getFirstname());
        $name->setSurname($shippingAddress->getLastname());
        $name->setTitle($shippingAddress->getPrefix());

        $addressPersonal = $this->addressPersonalFactory->create();
        $addressPersonal->setName($name);
        $addressPersonal->setCity($shippingAddress->getCity());
        $addressPersonal->setZip($shippingAddress->getPostcode());
        $addressPersonal->setState($shippingAddress->getRegion());
        $addressPersonal->setCountryCode($shippingAddress->getCountryId());
        $addressPersonal->setStreet(str_replace("\n", " ", $shippingAddress->getStreetFull()));
        $shipping->setAddress($addressPersonal);

        if ($this->addressComparer->isAddressTheSame($quote)) {
            $shipping->setAddressIndicator(self::SAME_AS_SHIPPING);
        } else {
            $shipping->setAddressIndicator(self::DIFFERENT_THAN_BILLING);
        }

        return $shipping;
    }
}
