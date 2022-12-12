<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Refund;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Worldline\PaymentCore\Api\Service\Refund\CreateRefundServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/RefundPaymentApi
 */
class CreateRefundService implements CreateRefundServiceInterface
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
     * Create refund by payment id
     *
     * @param string $paymentId
     * @param RefundRequest $refundRequest
     * @param int|null $storeId
     * @return RefundResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, RefundRequest $refundRequest, ?int $storeId = null): RefundResponse
    {
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->refundPayment($paymentId, $refundRequest);
        } catch (\Exception $e) {
            throw new LocalizedException(__('WorldLine refund has failed. Please contact the provider.'));
        }
    }
}
