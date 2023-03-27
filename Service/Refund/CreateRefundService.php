<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Refund;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Refund\CreateRefundServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Payments/operation/RefundPaymentApi
 */
class CreateRefundService implements CreateRefundServiceInterface
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
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('WorldLine refund has failed. Please contact the provider.'));
        }
    }
}
