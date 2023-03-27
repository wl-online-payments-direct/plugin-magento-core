<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use OnlinePayments\Sdk\Domain\CaptureResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Payment\CapturePaymentServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/CapturePaymentApi
 */
class CapturePaymentService implements CapturePaymentServiceInterface
{
    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProviderInterface $clientProvider,
        LoggerInterface $logger
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
        $this->logger = $logger;
    }

    /**
     * Capture request
     *
     * @param string $transactionId
     * @param CapturePaymentRequest $capturePaymentRequest
     * @param int|null $storeId
     * @return CaptureResponse
     * @throws LocalizedException
     */
    public function execute(
        string $transactionId,
        CapturePaymentRequest $capturePaymentRequest,
        ?int $storeId = null
    ): CaptureResponse {
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->capturePayment($transactionId, $capturePaymentRequest);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('CapturePayment request has failed'));
        }
    }
}
