<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Services;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Api\Service\Services\SurchargeCalculationServiceInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

class SurchargeCalculationService implements SurchargeCalculationServiceInterface
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
     * @param CalculateSurchargeRequest $requestBody
     * @param int|null $storeId
     * @return array
     * @throws LocalizedException
     */
    public function execute(CalculateSurchargeRequest $requestBody, ?int $storeId = null): array
    {
        try {
            $result = $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->services()
                ->surchargeCalculation($requestBody);

            return $result->getSurcharges();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(
                __('WorldLine surcharge calculation has failed. Please contact the provider.')
            );
        }
    }
}
