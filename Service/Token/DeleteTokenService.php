<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Token;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Token\DeleteTokenServiceInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

class DeleteTokenService implements DeleteTokenServiceInterface
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

    public function execute(string $token, ?int $storeId = null): void
    {
        try {
            $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->tokens()
                ->deleteToken($token);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('WorldLine delete token has failed. Please contact the provider.'));
        }
    }
}
