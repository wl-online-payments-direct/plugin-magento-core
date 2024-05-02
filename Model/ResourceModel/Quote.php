<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory as QuotePaymentCollectionFactory;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
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
     * @var array
     */
    private $quotes = [];

    public function __construct(
        QuotePaymentCollectionFactory $quotePaymentCollectionFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        $this->quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
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
        $collection = $this->quotePaymentCollectionFactory->create();
        $collection->addFieldToFilter('additional_information', ['like' => '%' . $paymentId . '%']);
        $collection->setOrder('payment_id');
        $collection->getSelect()->limit(1);
        $quotePayment = $collection->getFirstItem();
        if ($quotePayment->isEmpty()) {
            $this->logger->warning('No quote_payment entity with payment_id: ' . $paymentId);
            return null;
        }

        $collection = $this->quoteCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['eq' => $quotePayment->getQuoteId()]);
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
}
