<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * Implementation for GetPaymentDetailsApi
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/GetPaymentDetailsApi
 */
class GetPaymentDetailsService implements GetPaymentDetailsServiceInterface
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

    /**
     * @var array
     */
    private $cachedRequests = [];

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
     * Retrieve payment detail data
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return PaymentDetailsResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): PaymentDetailsResponse
    {
        if (isset($this->cachedRequests[$paymentId])) {
            return $this->cachedRequests[$paymentId];
        }

        try {
            $this->cachedRequests[$paymentId] = $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->getPaymentDetails($paymentId);

            return $this->cachedRequests[$paymentId];
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('GetPaymentDetailsApi request has failed'));
        }
    }
}
