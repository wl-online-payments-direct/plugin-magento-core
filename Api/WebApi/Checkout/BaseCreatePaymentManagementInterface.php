<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\WebApi\Checkout;

/**
 * Base interface for create payment service
 */
interface BaseCreatePaymentManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string redirect url
     */
    public function createRequest(
        int $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        ?\Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): string;

    /**
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param string $email
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string redirect url
     */
    public function createGuestRequest(
        string $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        string $email,
        ?\Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): string;
}
