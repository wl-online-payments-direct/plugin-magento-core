<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Services;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\StripTags;
use Worldline\PaymentCore\Api\Service\Services\TestConnectionServiceInterface;
use Worldline\PaymentCore\Model\ClientProvider;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

/**
 * @link https://support.direct.ingenico.com/documentation/api/reference/#tag/Services/operation/TestConnectionApi
 */
class TestConnectionService implements TestConnectionServiceInterface
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var StripTags
     */
    private $tagFilter;

    public function __construct(
        ClientProvider $clientProvider,
        WorldlineConfig $worldlineConfig,
        StripTags $tagFilter
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
        $this->tagFilter = $tagFilter;
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
            throw new LocalizedException(__('The server returned an error.'));
        }
    }
}
