<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\WebApi\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerQuotePreparer extends QuoteManagement
{
    /**
     * Call magento function to prepare address for customer quote validation
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepare(CartInterface $quote): void
    {
        $this->_prepareCustomerQuote($quote);
    }
}
