<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Services;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Services\TestConnectionServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/documentation/api/reference/#tag/Services/operation/TestConnectionApi
 */
class TestConnectionService implements TestConnectionServiceInterface
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
     * Test connection
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute(): string
    {
        try {
            $result = $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->services()
                ->testConnection();

            return (string) $result->getResult();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('The server returned an error.'));
        }
    }
}
