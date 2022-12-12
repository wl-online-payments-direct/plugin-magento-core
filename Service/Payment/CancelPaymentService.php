<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use Worldline\PaymentCore\Api\Service\Payment\CancelPaymentServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/CancelPaymentApi
 */
class CancelPaymentService implements CancelPaymentServiceInterface
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
     * Cancel payment by payment id
     *
     * @param string $paymentId
     * @param int|null $storeId
     * @return CancelPaymentResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, ?int $storeId = null): CancelPaymentResponse
    {
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->cancelPayment($paymentId);
        } catch (Exception $e) {
            throw new LocalizedException(__('CancelPaymentApi has failed. Please contact the provider.'));
        }
    }
}
