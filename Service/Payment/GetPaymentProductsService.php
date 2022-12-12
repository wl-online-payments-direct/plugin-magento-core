<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use Worldline\PaymentCore\Api\Service\GetPaymentProductsServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * Implementation for GetPaymentProducts
 *
 * @see: https://support.direct.ingenico.com/en/documentation/api/reference/#tag/Products/operation/GetPaymentProducts
 */
class GetPaymentProductsService implements GetPaymentProductsServiceInterface
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
            throw new LocalizedException(__('GetPaymentProducts request has failed'));
        }
    }
}
