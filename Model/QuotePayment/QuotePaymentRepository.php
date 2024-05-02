<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\QuotePayment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterfaceFactory;
use Worldline\PaymentCore\Api\QuotePaymentRepositoryInterface;
use Worldline\PaymentCore\Model\QuotePayment\ResourceModel\QuotePayment as QuotePaymentResource;
use Worldline\PaymentCore\Model\QuotePayment\ResourceModel\QuotePayment\CollectionFactory;

/**
 * Repository for worldline quote payment entity
 */
class QuotePaymentRepository implements QuotePaymentRepositoryInterface
{
    /**
     * @var QuotePaymentResource
     */
    private $quotePaymentResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var QuotePaymentInterfaceFactory
     */
    private $quotePaymentInterfaceFactory;

    public function __construct(
        QuotePaymentResource $quotePaymentResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        QuotePaymentInterfaceFactory $quotePaymentInterfaceFactory
    ) {
        $this->quotePaymentResource = $quotePaymentResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->quotePaymentInterfaceFactory = $quotePaymentInterfaceFactory;
    }

    public function save(QuotePaymentInterface $quotePayment): QuotePaymentInterface
    {
        $this->quotePaymentResource->save($quotePayment);
        return $quotePayment;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getItems();
    }

    public function get(int $paymentId): QuotePaymentInterface
    {
        $quotePayment = $this->quotePaymentInterfaceFactory->create();
        $this->quotePaymentResource->load($quotePayment, $paymentId, QuotePaymentInterface::PAYMENT_ID);

        return $quotePayment;
    }

    public function getByPaymentIdentifier(string $paymentIdentifier): QuotePaymentInterface
    {
        $quotePayment = $this->quotePaymentInterfaceFactory->create();
        $this->quotePaymentResource->load($quotePayment, $paymentIdentifier, QuotePaymentInterface::PAYMENT_IDENTIFIER);

        return $quotePayment;
    }
}
