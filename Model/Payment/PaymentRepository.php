<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterfaceFactory;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment as PaymentResource;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment\CollectionFactory;

/**
 * Repository for worldline payment entity
 */
class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentInterfaceFactory;

    public function __construct(
        PaymentResource $paymentResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        PaymentInterfaceFactory $paymentInterfaceFactory
    ) {
        $this->paymentResource = $paymentResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->paymentInterfaceFactory = $paymentInterfaceFactory;
    }

    public function save(PaymentInterface $payment): PaymentInterface
    {
        $this->paymentResource->save($payment);
        return $payment;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getItems();
    }

    public function get(string $incrementId): PaymentInterface
    {
        $payment = $this->paymentInterfaceFactory->create();
        $this->paymentResource->load($payment, $incrementId, PaymentInterface::INCREMENT_ID);

        return $payment;
    }

    public function deleteByIncrementId(string $incrementId): void
    {
        $payment = $this->paymentInterfaceFactory->create();
        $this->paymentResource->load($payment, $incrementId, PaymentInterface::INCREMENT_ID);
        if ($payment->getId()) {
            $this->paymentResource->delete($payment);
        }
    }
}
