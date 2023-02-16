<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\WebApi\Checkout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Worldline\PaymentCore\Api\WebApi\Checkout\QuoteManagerInterface;

class QuoteManager implements QuoteManagerInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param int $cartId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    public function getQuote(int $cartId): CartInterface
    {
        return $this->cartRepository->get($cartId);
    }

    /**
     * @param string $cartId
     * @param string $email
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    public function getQuoteForGuest(string $cartId, string $email): CartInterface
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->cartRepository->get($quoteIdMask->getQuoteId());
        $quote->setCustomerEmail($email);

        // compatibility with magento 2.3.7
        $quote->setCustomerIsGuest(true);

        return $quote;
    }
}
