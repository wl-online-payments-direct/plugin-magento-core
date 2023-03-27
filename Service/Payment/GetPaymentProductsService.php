<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentProductsServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * Implementation for GetPaymentProducts
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Products/operation/GetPaymentProducts
 */
class GetPaymentProductsService implements GetPaymentProductsServiceInterface
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
     * @param GetPaymentProductsParams $queryParams
     * @param int|null $storeId
     * @return GetPaymentProductsResponse
     * @throws LocalizedException
     */
    public function execute(GetPaymentProductsParams $queryParams, ?int $storeId = null): GetPaymentProductsResponse
    {
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->products()
                ->getPaymentProducts($queryParams);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('GetPaymentProducts request has failed'));
        }
    }
}
