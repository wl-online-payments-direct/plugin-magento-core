<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\FraudManagerInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;
use Worldline\PaymentCore\Api\Data\PaymentInterface;

/**
 * Manager for worldline payment entity
 */
class PaymentDataManager implements PaymentDataManagerInterface
{
    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var PaymentManagerInterface
     */
    private $paymentManager;

    /**
     * @var FraudManagerInterface
     */
    private $fraudManager;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    public function __construct(
        PaymentManagerInterface $paymentManager,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        FraudManagerInterface $fraudManager,
        QuoteResource $quoteResource
    ) {
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->paymentManager = $paymentManager;
        $this->fraudManager = $fraudManager;
        $this->quoteResource = $quoteResource;
    }

    /**
     * Validate and save payment, transaction, fraud information
     *
     * @param PaymentResponse $paymentResponse
     * @return void
     */
    public function savePaymentData(PaymentResponse $paymentResponse): void
    {
        if (!$this->isValid($paymentResponse)) {
            return;
        }

        $wlPayment = $this->paymentManager->savePayment($paymentResponse);
        $this->transactionWLResponseManager->saveTransaction($paymentResponse);
        $this->fraudManager->saveFraudInformation($paymentResponse, $wlPayment);
    }

    private function isValid(PaymentResponse $paymentResponse): bool
    {
        $paymentId = (string)(int)$paymentResponse->getId();
        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($paymentId);
        if (!$quote->getId()) {
            return false;
        }

        $responseId = (int)$paymentResponse->getId();
        $paymentId = (int)$quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID);

        return $responseId === $paymentId;
    }
}
