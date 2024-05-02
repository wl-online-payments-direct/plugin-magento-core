<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterfaceFactory;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\Webhook\PlaceOrderManagerInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceValidator;
use Worldline\PaymentCore\Model\QuotePayment\QuotePaymentRepository;

/**
 * Helper for a place order processor
 */
class PlaceOrderManager implements PlaceOrderManagerInterface
{
    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var CanPlaceValidator
     */
    private $canPlaceValidator;

    /**
     * @var CanPlaceOrderContextInterfaceFactory
     */
    private $canPlaceOrderContextFactory;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    /**
     * @var QuotePaymentRepository
     */
    private $quotePaymentRepository;

    public function __construct(
        QuoteResourceInterface $quoteResource,
        CanPlaceValidator $canPlaceValidator,
        CanPlaceOrderContextInterfaceFactory $canPlaceOrderContextFactory,
        PaymentIdFormatterInterface $paymentIdFormatter,
        QuotePaymentRepository $quotePaymentRepository
    ) {
        $this->quoteResource = $quoteResource;
        $this->canPlaceValidator = $canPlaceValidator;
        $this->canPlaceOrderContextFactory = $canPlaceOrderContextFactory;
        $this->paymentIdFormatter = $paymentIdFormatter;
        $this->quotePaymentRepository = $quotePaymentRepository;
    }

    public function getValidatedQuote(WebhooksEvent $webhookEvent): ?CartInterface
    {
        /** @var PaymentResponse $paymentResponse */
        $paymentResponse = $webhookEvent->getPayment();
        $paymentId = $this->paymentIdFormatter->validateAndFormat((string)$paymentResponse->getId());
        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($paymentId);
        if (!$quote || !$quote->getId()) {
            return null;
        }

        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('payment_id', $paymentId);
        $quotePayment = $this->quotePaymentRepository->getByPaymentIdentifier($paymentId);
        $payment->setMethod($quotePayment->getMethod());
        $this->quoteResource->save($quote);

        if (!$this->isValid($paymentResponse, $quote)) {
            return null;
        }

        return $quote;
    }

    private function isValid(PaymentResponse $paymentResponse, CartInterface $quote): bool
    {
        $context = $this->canPlaceOrderContextFactory->create();
        $context->setStatusCode((int)$paymentResponse->getStatusOutput()->getStatusCode());
        $context->setWorldlinePaymentId((string)$paymentResponse->getId());
        $context->setStoreId($quote->getStoreId());

        try {
            $this->canPlaceValidator->validate($context);
            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
