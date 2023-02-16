<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link: https://support.direct.ingenico.com/documentation/api/reference/#operation/GetPaymentApi
 */
class GetPaymentService implements GetPaymentServiceInterface
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var array
     */
    private $cachedRequests = [];

    public function __construct(
        ClientProvider $clientProvider,
        WorldlineConfig $worldlineConfig
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
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
            throw new LocalizedException(__('GetPaymentApi request has failed. Please contact the provider.'));
        }
    }
}
