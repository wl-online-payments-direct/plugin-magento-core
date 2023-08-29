<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Quote\Address;

use Magento\Quote\Api\Data\CartInterface;

class Comparer
{
    public function isAddressTheSame(CartInterface $quote): bool
    {
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        if ($billingAddress->getCity() !== $shippingAddress->getCity()) {
            return false;
        }

        if ($billingAddress->getPostcode() !== $shippingAddress->getPostcode()) {
            return false;
        }

        if ($billingAddress->getRegion() !== $shippingAddress->getRegion()) {
            return false;
        }

        if ($billingAddress->getCountryId() !== $shippingAddress->getCountryId()) {
            return false;
        }

        if ($billingAddress->getStreetFull() !== $shippingAddress->getStreetFull()) {
            return false;
        }

        return true;
    }
}
