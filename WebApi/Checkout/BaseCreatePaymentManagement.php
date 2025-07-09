<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\WebApi\Checkout;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\CustomerManagement;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterfaceFactory;
use Worldline\PaymentCore\Api\QuotePaymentRepositoryInterface;
use Worldline\PaymentCore\Api\QuoteRestorationInterface;
use Worldline\PaymentCore\Api\WebApi\Checkout\BaseCreatePaymentManagementInterface;
use Worldline\PaymentCore\Api\WebApi\Checkout\QuoteManagerInterface;
use Worldline\PaymentCore\Model\DataAssigner\DataAssignerInterface;
use Worldline\PaymentCore\Model\QuotePayment\QuotePaymentRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BaseCreatePaymentManagement implements BaseCreatePaymentManagementInterface
{
    /**
     * @var QuoteManagerInterface
     */
    private $quoteManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * @var QuoteRestorationInterface
     */
    private $quoteRestoration;

    /**
     * @var CustomerManagement
     */
    private $customerManagement;

    /**
     * @var QuotePaymentInterfaceFactory
     */
    private $wlQuotePaymentFactory;

    /**
     * @var QuotePaymentRepository
     */
    private $wlQuotePaymentRepository;

    /**
     * @var DataAssignerInterface[]
     */
    private $dataAssignerPool;

    public function __construct(
        QuoteManagerInterface $quoteManager,
        CartRepositoryInterface $cartRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        QuoteRestorationInterface $quoteRestoration,
        CustomerManagement $customerManagement,
        QuotePaymentInterfaceFactory $wlQuotePaymentFactory,
        QuotePaymentRepositoryInterface $wlQuotePaymentRepository,
        array $dataAssignerPool = []
    ) {
        $this->quoteManager = $quoteManager;
        $this->cartRepository = $cartRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteRestoration = $quoteRestoration;
        $this->customerManagement = $customerManagement;
        $this->wlQuotePaymentFactory = $wlQuotePaymentFactory;
        $this->wlQuotePaymentRepository = $wlQuotePaymentRepository;
        $this->dataAssignerPool = $dataAssignerPool;
    }

    /**
     * Retrieve redirect url
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     *
     * @return string redirect url
     */
    public function createRequest(
        int $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): string {
        $quote = $this->quoteManager->getQuote($cartId);

        return $this->process($quote, $paymentMethod, $billingAddress);
    }

    /**
     * Retrieve redirect url for quest user
     *
     * @param string $cartId
     * @param PaymentInterface $paymentMethod
     * @param string $email
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     *
     * @return string redirect url
     */
    public function createGuestRequest(
        string $cartId,
        PaymentInterface $paymentMethod,
        string $email,
        ?AddressInterface $billingAddress = null
    ): string {
        $quote = $this->quoteManager->getQuoteForGuest($cartId, $email);

        return $this->process($quote, $paymentMethod, $billingAddress);
    }

    private function process(
        CartInterface $quote,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): string {
        if (!$quote->isVirtual()) {
            $this->validateAddress($quote->getShippingAddress());
            $this->validateAddress($quote->getBillingAddress());
        }

        $this->paymentInformationManagement->savePaymentInformation($quote->getId(), $paymentMethod, $billingAddress);

        if (!$quote->getCustomerIsGuest()) {
            if ($quote->getCustomerId()) {
                $this->customerManagement->validateAddresses($quote);
            }
        }

        // For PWA additional information is used, for luma - additional_data
        $additionalData = array_merge(
            (array)$paymentMethod->getAdditionalInformation(),
            (array)$paymentMethod->getAdditionalData()
        );

        $quote->reserveOrderId();

        $wlQuotePayment = $this->wlQuotePaymentFactory->create();
        foreach ($this->dataAssignerPool as $dataAssigner) {
            $dataAssigner->assign($quote->getPayment(), $wlQuotePayment, $additionalData);
        }

        $quote->setIsActive(false);
        $this->quoteRestoration->preserveQuoteId((int)$quote->getId());
        $this->cartRepository->save($quote);
        $this->wlQuotePaymentRepository->save($wlQuotePayment);

        return (string) $quote->getPayment()->getWlRedirectUrl();
    }

    /**
     * Magento does not validate addresses in the backend if they are added on the checkout page.
     * The validation happens afterward when Magento creates the order and saves the addresses.
     * It breaks the order creation process.
     * To prevent this we need to validate the addresses before the payment.
     *
     * @param AddressInterface|null $address
     * @return void
     * @throws LocalizedException
     */
    private function validateAddress(?AddressInterface $address = null): void
    {
        if (!$address) {
            return;
        }

        $validationResult = $address->validate();
        if ($validationResult && is_array($validationResult)) {
            throw new LocalizedException(current($validationResult));
        }
    }
}
