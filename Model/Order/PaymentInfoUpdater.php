<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionUpdater;

class PaymentInfoUpdater
{
    /**
     * @var GetPaymentDetailsServiceInterface
     */
    private $detailsRequest;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentManagerInterface
     */
    private $paymentManager;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    /**
     * @var TransactionUpdater
     */
    private $transactionUpdater;

    public function __construct(
        GetPaymentDetailsServiceInterface $detailsRequest,
        LoggerInterface $logger,
        OrderFactory $orderFactory,
        PaymentManagerInterface $paymentManager,
        PaymentIdFormatterInterface $paymentIdFormatter,
        TransactionUpdater $transactionUpdater
    ) {
        $this->detailsRequest = $detailsRequest;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->paymentManager = $paymentManager;
        $this->paymentIdFormatter = $paymentIdFormatter;
        $this->transactionUpdater = $transactionUpdater;
    }

    /**
     * Update payment details
     *
     * @param string $incrementId
     * @param int|null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function updateForIncrementId(string $incrementId, ?int $storeId = null): bool
    {
        try {
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            if (!$order->getPayment()) {
                return false;
            }

            $paymentId = $order->getPayment()->getAdditionalInformation()[PaymentInterface::PAYMENT_ID] ?? null;
            if (!$paymentId) {
                return false;
            }

            $paymentId = $this->paymentIdFormatter->validateAndFormat($paymentId, true);
            $response = $this->detailsRequest->execute($paymentId, $storeId);
            $operations = $response->getOperations();
            if (!$operations) {
                return false;
            }

            $this->paymentManager->updatePayment($response);
            $this->transactionUpdater->update($response);

            return true;
        } catch (LocalizedException $e) {
            $this->logger->warning($e->getMessage());
            throw new LocalizedException(__('Payment details update has failed'));
        }
    }
}
