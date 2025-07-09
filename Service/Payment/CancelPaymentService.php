<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CancelPaymentRequest;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Payment\CancelPaymentServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/CancelPaymentApi
 */
class CancelPaymentService implements CancelPaymentServiceInterface
{
    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientProviderInterface $clientProvider,
        WorldlineConfig $worldlineConfig,
        LoggerInterface $logger
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
        $this->logger = $logger;
    }

    /**
     * Cancel payment by payment id
     *
     * @param PaymentResponse $payment
     * @param int|null $storeId
     * @return CancelPaymentResponse
     * @throws LocalizedException
     */
    public function execute(PaymentResponse $payment, ?int $storeId = null): CancelPaymentResponse
    {
        try {
            $cancelPaymentRequest = new CancelPaymentRequest();
            $cancelPaymentRequest->setIsFinal(true);
            $cancelPaymentRequest->setAmountOfMoney($payment->getPaymentOutput()->getAmountOfMoney());

            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->cancelPayment($payment->id, $cancelPaymentRequest);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('CancelPaymentApi has failed. Please contact the provider.'));
        }
    }
}
