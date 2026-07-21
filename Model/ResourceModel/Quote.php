<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory as QuotePaymentCollectionFactory;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\QuotePaymentRepositoryInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;

class Quote implements QuoteResourceInterface
{
    /**
     * @var QuotePaymentCollectionFactory
     */
    private $quotePaymentCollectionFactory;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuotePaymentRepositoryInterface
     */
    private $quotePaymentRepository;

    /**
     * @var array
     */
    private $quotes = [];

    public function __construct(
        QuotePaymentCollectionFactory $quotePaymentCollectionFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger,
        QuotePaymentRepositoryInterface $quotePaymentRepository
    ) {
        $this->quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->quotePaymentRepository = $quotePaymentRepository;
    }

    public function getQuoteByReservedOrderId(string $reservedOrderId): ?CartInterface
    {
        if (empty($this->quotes[$reservedOrderId])) {
            // collection because the refund doesn't work in multishop context
            $collection = $this->quoteCollectionFactory->create();
            $collection->addFieldToFilter('reserved_order_id', ['eq' => $reservedOrderId]);
            $collection->getSelect()->limit(1);
            $quote = $collection->getFirstItem();
            if ($quote->isEmpty()) {
                return null;
            }
            // need for load additional attributes
            $loadedQuote = $this->cartRepository->get($quote->getId());
            $this->quotes[$reservedOrderId] = $loadedQuote;
        }

        return $this->quotes[$reservedOrderId];
    }

    public function getQuoteByWorldlinePaymentId(string $paymentId): ?CartInterface
    {
        $quoteId = $this->resolveQuoteId($paymentId);
        if ($quoteId === null) {
            $this->logger->warning('No quote_payment entity with payment_id: ' . $paymentId);
            return null;
        }

        $collection = $this->quoteCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['eq' => $quoteId]);
        $collection->getSelect()->limit(1);
        $quote = $collection->getFirstItem();

        return $this->cartRepository->get($quote->getId());
    }

    public function setPaymentIdAndSave(CartInterface $quote, int $paymentProductId): void
    {
        $quote->getPayment()
            ->setAdditionalInformation(PaymentInterface::PAYMENT_PRODUCT_ID, $paymentProductId);
        $this->save($quote);
    }

    public function save(CartInterface $quote): void
    {
        $this->cartRepository->save($quote);
    }

    /**
     * Resolve the Magento quote id for a Worldline payment id or a hosted-tokenization id.
     *
     * The Worldline payment id is stored in the canonical, indexed
     * worldline_quote_payment_information.payment_identifier column, so it is resolved via an exact
     * match (no full-table scan, no LIKE wildcards). The hosted-tokenization id only exists inside
     * the serialized quote_payment.additional_information blob, so it falls back to a LIKE match with
     * LIKE meta-characters escaped, preventing a wildcard-only input from matching foreign quotes.
     *
     * @param string $paymentId
     * @return int|null
     */
    private function resolveQuoteId(string $paymentId): ?int
    {
        $wlQuotePayment = $this->quotePaymentRepository->getByPaymentIdentifier($paymentId);
        $nativePaymentId = (int) $wlQuotePayment->getPaymentId();
        $quoteId = $nativePaymentId > 0 ? $this->getQuoteIdByNativePaymentId($nativePaymentId) : null;
        if ($quoteId !== null) {
            return $quoteId;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $escaped = addcslashes($paymentId, '\\%_');

        $collection = $this->quotePaymentCollectionFactory->create();
        $collection->addFieldToFilter('additional_information', ['like' => '%' . $escaped . '%']);
        $collection->setOrder('payment_id');
        $collection->getSelect()->limit(1);
        $quotePayment = $collection->getFirstItem();
        if ($quotePayment->isEmpty()) {
            return null;
        }

        return (int) $quotePayment->getQuoteId();
    }

    /**
     * Load the quote id from the native quote_payment table by its primary key.
     *
     * @param int $nativePaymentId
     * @return int|null
     */
    private function getQuoteIdByNativePaymentId(int $nativePaymentId): ?int
    {
        $collection = $this->quotePaymentCollectionFactory->create();
        $collection->addFieldToFilter('payment_id', ['eq' => $nativePaymentId]);
        $collection->getSelect()->limit(1);
        $quotePayment = $collection->getFirstItem();
        if ($quotePayment->isEmpty()) {
            return null;
        }

        return (int) $quotePayment->getQuoteId();
    }
}
