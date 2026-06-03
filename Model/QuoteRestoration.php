<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\QuoteRestorationInterface;

/**
 * Restore quote after the payment page
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteRestoration implements QuoteRestorationInterface
{
    private const SECTION_DATA_IDS_COOKIE = 'section_data_ids';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function preserveQuoteId(int $quoteId): void
    {
        $this->checkoutSession->setWlShiftedQuoteRecoveryId(null);
        $this->checkoutSession->setWlQuoteRecoveryId($quoteId);
        // Detach the inactive quote from the session so the server returns an empty cart
        // on later requests. restoreQuote() will re-attach it via replaceQuote() if
        // the payment fails and the customer returns through the normal cart-recovery flow.
        $this->checkoutSession->setQuoteId(null);
        $this->invalidateCartSection();
    }

    /**
     * Force the browser to re-fetch the cart customer-data section on the next page load.
     * Without this, the mini-cart localStorage cache keeps showing items even after the
     * quote is deactivated, because section_data_ids is never bumped during payment initiation.
     */
    private function invalidateCartSection(): void
    {
        $existing = $this->cookieManager->getCookie(self::SECTION_DATA_IDS_COOKIE);
        $sectionIds = $existing ? json_decode($existing, true) : [];
        if (!is_array($sectionIds)) {
            $sectionIds = [];
        }

        $sectionIds['cart'] = time();

        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(3600)
            ->setPath('/')
            ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            self::SECTION_DATA_IDS_COOKIE,
            json_encode($sectionIds),
            $metadata
        );
    }

    /**
     * Shift functionality is developed on purpose.
     * The requirements are to restore the cart by clicking on the "back" button.
     * Magento does not contain any information that the return has been done directly from the payment page.
     * So it is still possible to emulate cart restoring without clicking on the "back" button.
     *
     * With this functionality we are sure the flow is exactly the same:
     * - make the quote inactive and save the quote id in the session during redirection on the payment page
     * - click back
     * - go to the checkout page - the shift is done here
     * - as the quote is empty, Magento redirects the checkout/cart page
     * - on the checkout/cart page the quote id is taken from getWlShiftedQuoteRecoveryId
     * and the id is used for quote recovery
     *
     * @return void
     */
    public function shiftQuoteId(): void
    {
        $quoteId = $this->checkoutSession->getWlQuoteRecoveryId();

        if (!$quoteId) {
            return;
        }

        $this->checkoutSession->setWlQuoteRecoveryId(null);
        $this->checkoutSession->setWlShiftedQuoteRecoveryId($quoteId);
    }

    public function restoreQuote(): void
    {
        if (!$quoteId = $this->checkoutSession->getWlShiftedQuoteRecoveryId()) {
            return;
        }

        $this->checkoutSession->setWlShiftedQuoteRecoveryId(null);

        try {
            $quote = $this->cartRepository->get($quoteId);

            $reservedOrderId = $quote->getReservedOrderId();
            if ($reservedOrderId) {
                $order = $this->orderFactory->create()->loadByIncrementId($reservedOrderId);
                if ($order->getId()) {
                    return;
                }
            }

            $quote->setIsActive(true);

            $this->cartRepository->save($quote);
            $this->checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
        }
    }
}
