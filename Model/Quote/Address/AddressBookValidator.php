<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Quote\Address;

use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Validates, before the payment, the quote addresses that Magento will save to the customer's address book
 * when the order is placed.
 *
 * Magento runs the "customer_address/save" validators (name, street, city, telephone) only in
 * AddressRepository::save(), which QuoteManagement::_prepareCustomerQuote() calls while placing the order.
 * With a redirect payment that is after the customer has paid, so a failing validator leaves a paid payment
 * without an order. CustomerManagement::validateAddresses() does not cover this: it only checks addresses
 * that already have a customer_address_id, so an address a logged-in customer has just typed is skipped.
 *
 * @see \Magento\Quote\Model\QuoteManagement::_prepareCustomerQuote
 * @see \Magento\Quote\Model\CustomerManagement::validateAddresses
 */
class AddressBookValidator
{
    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    public function __construct(ValidatorFactory $validatorFactory, AddressFactory $addressFactory)
    {
        $this->validatorFactory = $validatorFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Throw if an address that will be saved to the address book fails the address book validators.
     *
     * @param CartInterface $quote
     * @return void
     * @throws ValidatorException
     */
    public function validate(CartInterface $quote): void
    {
        if ($quote->getCustomerIsGuest() || !$quote->getCustomerId()) {
            return;
        }

        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        if ($shipping && !$shipping->getSameAsBilling() && $this->willBeSaved($shipping)) {
            $this->validateAddress($shipping);
        }

        $billing = $quote->getBillingAddress();
        if ($billing && $this->willBeSaved($billing)) {
            $this->validateAddress($billing);
        }
    }

    /**
     * Same condition as QuoteManagement::_prepareCustomerQuote() uses to decide whether to save the address.
     *
     * @param AddressInterface $address
     * @return bool
     */
    private function willBeSaved(AddressInterface $address): bool
    {
        return !$address->getCustomerId() || $address->getSaveInAddressBook();
    }

    /**
     * Run the validators AddressRepository::save() runs.
     *
     * @param AddressInterface $address
     * @return void
     * @throws ValidatorException
     */
    private function validateAddress(AddressInterface $address): void
    {
        $addressModel = $this->addressFactory->create();
        $addressModel->updateData($address->exportCustomerAddress());

        $validator = $this->validatorFactory->createValidator('customer_address', 'save');
        if (!$validator->isValid($addressModel)) {
            throw new ValidatorException(null, null, $validator->getMessages());
        }
    }
}
