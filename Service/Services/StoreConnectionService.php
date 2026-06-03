<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Services;

use Worldline\PaymentCore\Api\Config\WorldlineConfigInterface;
use Worldline\PaymentCore\Api\Service\Services\StoreConnectionServiceInterface;

class StoreConnectionService implements StoreConnectionServiceInterface
{
    /**
     * @var WorldlineConfigInterface
     */
    private $worldlineConfig;

    public function __construct(WorldlineConfigInterface $worldlineConfig)
    {
        $this->worldlineConfig = $worldlineConfig;
    }

    public function execute(int $storeId): bool
    {
        return (bool) $this->worldlineConfig->getMerchantId($storeId)
            && (bool) $this->worldlineConfig->getApiKey($storeId)
            && (bool) $this->worldlineConfig->getApiSecret($storeId);
    }
}
