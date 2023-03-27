<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\WebApi\Checkout;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\QuoteRestorationInterface;
use Worldline\PaymentCore\Api\WebApi\Checkout\BaseCreatePaymentManagementInterface;
use Worldline\PaymentCore\Api\WebApi\Checkout\QuoteManagerInterface;
use Worldline\PaymentCore\Model\DataAssigner\DataAssignerInterface;

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
     * @var DataAssignerInterface[]
     */
    private $dataAssignerPool;

    public function __construct(
        QuoteManagerInterface $quoteManager,
        CartRepositoryInterface $cartRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        QuoteRestorationInterface $quoteRestoration,
        array $dataAssignerPool = []
    ) {
        $this->quoteManager = $quoteManager;
        $this->cartRepository = $cartRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteRestoration = $quoteRestoration;
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
        AddressInterface $billingAddress = null
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
        AddressInterface $billingAddress = null
    ): string {
        $quote = $this->quoteManager->getQuoteForGuest($cartId, $email);

        return $this->process($quote, $paymentMethod, $billingAddress);
    }

    private function process(
        CartInterface $quote,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): string {
        $this->paymentInformationManagement->savePaymentInformation($quote->getId(), $paymentMethod, $billingAddress);

        // For PWA additional information is used, for luma - additional_data
        $additionalData = array_merge(
            (array)$paymentMethod->getAdditionalInformation(),
            (array)$paymentMethod->getAdditionalData()
        );

        $quote->reserveOrderId();

        foreach ($this->dataAssignerPool as $dataAssigner) {
            $dataAssigner->assign($quote->getPayment(), $additionalData);
        }

        $quote->setIsActive(false);
        $this->quoteRestoration->preserveQuoteId((int)$quote->getId());
        $this->cartRepository->save($quote);

        return (string) $quote->getPayment()->getWlRedirectUrl();
    }
}