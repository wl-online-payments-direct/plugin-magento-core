<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory as QuotePaymentCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

class Quote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuotePaymentCollectionFactory
     */
    private $quotePaymentCollectionFactory;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var array
     */
    private $quotes = [];

    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        QuotePaymentCollectionFactory $quotePaymentCollectionFactory,
        QuoteCollectionFactory $quoteCollectionFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    public function getQuoteByReservedOrderId(string $reservedOrderId): CartInterface
    {
        if (empty($this->quotes[$reservedOrderId])) {
            $collection = $this->quoteCollectionFactory->create();
            $collection->addFieldToFilter('reserved_order_id', ['eq' => $reservedOrderId]);
            $collection->getSelect()->limit(1);
            $this->quotes[$reservedOrderId] = $collection->getFirstItem();
        }

        return $this->quotes[$reservedOrderId];
    }

    public function getQuoteByWorldlinePaymentId(string $paymentId): CartInterface
    {
        $collection = $this->quotePaymentCollectionFactory->create();
        $collection->addFieldToFilter('additional_information', ['like' => '%' . $paymentId . '%']);
        $collection->setOrder('payment_id');
        $collection->getSelect()->limit(1);
        $quotePayment = $collection->getFirstItem();

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $quotePayment->getQuoteId());

        return $quote;
    }

    public function saveCart(CartInterface $cart)
    {
        $this->quoteResource->save($cart);
    }
}
