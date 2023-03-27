<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link: https://support.direct.ingenico.com/documentation/api/reference/#operation/GetPaymentApi
 */
class GetPaymentService implements GetPaymentServiceInterface
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
     * Retrieve payment information
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return PaymentResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): PaymentResponse
    {
        if (isset($this->cachedRequests[$paymentId])) {
            return $this->cachedRequests[$paymentId];
        }

        try {
            $this->cachedRequests[$paymentId] = $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->getPayment($paymentId);

            return $this->cachedRequests[$paymentId];
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('GetPaymentApi request has failed. Please contact the provider.'));
        }
    }
}
