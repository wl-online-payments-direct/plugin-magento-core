<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\WebApi\Checkout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

interface QuoteManagerInterface
{
    /**
     * @param int $cartId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    public function getQuote(int $cartId): CartInterface;

    /**
     * @param string $cartId
     * @param string $email
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    public function getQuoteForGuest(string $cartId, string $email): CartInterface;
}
