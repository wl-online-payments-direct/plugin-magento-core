<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Transaction;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Worldline\PaymentCore\Api\Data\TransactionInterfaceFactory;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\Transaction\ResourceModel\Transaction as TransactionResource;

/**
 * Manage to format, fill and save transaction data
 */
class TransactionWLResponseManager implements TransactionWLResponseManagerInterface
{
    /**
     * @var TransactionInterfaceFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var TransactionResource
     */
    private $transactionResource;

    public function __construct(
        TransactionInterfaceFactory $transactionFactory,
        TransactionRepositoryInterface $transactionRepository,
        TransactionResource $transactionResource
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->transactionResource = $transactionResource;
    }

    /**
     * Fill and save transaction
     *
     * @param DataObject $worldlineResponse (PaymentResponse|RefundResponse)
     * @return void
     * @throws LocalizedException
     */
    public function saveTransaction(DataObject $worldlineResponse): void
    {
        if ($this->transactionResource->isSaved((string)$worldlineResponse->getId())) {
            return;
        }

        $output = $this->getOutput($worldlineResponse);

        $amount = (int)$output->getAmountOfMoney()->getAmount();
        if ($worldlineResponse instanceof PaymentResponse || $worldlineResponse instanceof PaymentDetailsResponse) {
            if ($output->getSurchargeSpecificOutput()) {
                $amount += (int)$output->getSurchargeSpecificOutput()->getSurchargeAmount()->getAmount();
            }
        }

        $transaction = $this->transactionFactory->create();
        $transaction->setIncrementId((string)$output->getReferences()->getMerchantReference());
        $transaction->setStatus((string)$worldlineResponse->getStatus());
        $transaction->setStatusCode((int)$worldlineResponse->getStatusOutput()->getStatusCode());
        $transaction->setTransactionId((string)$worldlineResponse->getId());
        $transaction->setAmount($amount);
        $transaction->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
        $this->transactionRepository->save($transaction);
    }

    /**
     * Extract output object
     *
     * @param DataObject $response
     * @return DataObject
     * @throws LocalizedException
     */
    private function getOutput(DataObject $response): DataObject
    {
        $output = null;
        if ($response instanceof PaymentResponse || $response instanceof PaymentDetailsResponse) {
            $output = $response->getPaymentOutput();
        }

        if ($response instanceof RefundResponse) {
            $output = $response->getRefundOutput();
        }

        if (!$output) {
            throw new LocalizedException(__('Invalid output model'));
        }

        return $output;
    }
}
