<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * Implementation for GetPaymentDetailsApi
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/GetPaymentDetailsApi
 */
class GetPaymentDetailsService implements GetPaymentDetailsServiceInterface
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    public function __construct(
        ClientProvider $clientProvider,
        WorldlineConfig $worldlineConfig
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
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
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->getPaymentDetails($paymentId);
        } catch (\Exception $e) {
            throw new LocalizedException(__('GetPaymentDetailsApi request has failed'));
        }
    }
}
