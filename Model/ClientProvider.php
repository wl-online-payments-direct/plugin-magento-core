<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use OnlinePayments\Sdk\Client;
use OnlinePayments\Sdk\ClientFactory;
use OnlinePayments\Sdk\CommunicatorConfigurationFactory;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\OnlinePayments\Sdk\Communicator;
use Worldline\PaymentCore\OnlinePayments\Sdk\CommunicatorFactory;

class ClientProvider implements ClientProviderInterface
{
    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var CommunicatorConfigurationFactory
     */
    private $communicatorConfigurationFactory;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var CommunicatorFactory
     */
    private $communicatorFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function __construct(
        WorldlineConfig $worldlineConfig,
        CommunicatorConfigurationFactory $communicatorConfigurationFactory,
        CommunicatorFactory $communicatorFactory,
        ClientFactory $clientFactory
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->communicatorConfigurationFactory = $communicatorConfigurationFactory;
        $this->communicatorFactory = $communicatorFactory;
        $this->clientFactory = $clientFactory;
    }

    public function getClient(?int $storeId = null): Client
    {
        if (!$this->client) {
            $this->client = $this->clientFactory->create(['communicator' => $this->getCommunicator($storeId)]);
        }

        return $this->client;
    }

    private function getCommunicator(?int $storeId = null): Communicator
    {
        $communicatorConfiguration = $this->communicatorConfigurationFactory->create([
            'apiKeyId' => $this->worldlineConfig->getApiKey($storeId),
            'apiSecret' => $this->worldlineConfig->getApiSecret($storeId),
            'apiEndpoint' => $this->worldlineConfig->getApiEndpoint($storeId),
            'integrator' => 'Ingenico',
        ]);

        return $this->communicatorFactory->create(['communicatorConfiguration' => $communicatorConfiguration]);
    }
}
