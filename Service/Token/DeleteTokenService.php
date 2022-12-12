<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Token;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Api\Service\Token\DeleteTokenServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

class DeleteTokenService implements DeleteTokenServiceInterface
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

    public function execute(string $token, ?int $storeId = null): void
    {
        try {
            $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->tokens()
                ->deleteToken($token);
        } catch (\Exception $e) {
            throw new LocalizedException(__('WorldLine delete token has failed. Please contact the provider.'));
        }
    }
}
