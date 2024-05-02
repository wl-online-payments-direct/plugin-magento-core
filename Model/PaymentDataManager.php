<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\FraudManagerInterface;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;

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
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    public function __construct(
        PaymentManagerInterface $paymentManager,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        FraudManagerInterface $fraudManager,
        QuoteResourceInterface $quoteResource,
        PaymentIdFormatterInterface $paymentIdFormatter
    ) {
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->paymentManager = $paymentManager;
        $this->fraudManager = $fraudManager;
        $this->quoteResource = $quoteResource;
        $this->paymentIdFormatter = $paymentIdFormatter;
    }

    /**
     * Validate and save payment, transaction, fraud information
     *
     * @param PaymentResponse|PaymentDetailsResponse $paymentResponse
     * @return void
     */
    public function savePaymentData($paymentResponse): void
    {
        if (!$this->isValid($paymentResponse)) {
            return;
        }

        $wlPayment = $this->paymentManager->savePayment($paymentResponse);
        $this->transactionWLResponseManager->saveTransaction($paymentResponse);
        $this->fraudManager->saveFraudInformation($paymentResponse, $wlPayment);
    }

    /**
     * @param PaymentResponse|PaymentDetailsResponse $paymentResponse
     * @return bool
     */
    private function isValid($paymentResponse): bool
    {
        $wlPaymentId = $this->paymentIdFormatter->validateAndFormat($paymentResponse->getId());
        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($wlPaymentId);
        if (!$quote || !$quote->getId()) {
            return false;
        }

        $paymentId = (string) $quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID);
        $paymentId = $this->paymentIdFormatter->validateAndFormat($paymentId);

        return $wlPaymentId === $paymentId;
    }
}
