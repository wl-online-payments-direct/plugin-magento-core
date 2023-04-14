<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\ConnectionTest;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Worldline\PaymentCore\Api\Service\Services\TestConnectionServiceInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;

class FromAjaxRequest
{
    public const ENV_MODE = 'environment_mode';
    public const API_SECRET = ['api_secret', 'api_secret_prod'];
    public const API_ENDPOINT = ['testing_api_url', 'production_api_url'];
    public const API_KEY = ['api_key', 'api_key_prod'];
    public const MERCHANT_ID = ['merchant_id', 'merchant_id_prod'];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var int
     */
    private $envMode;

    /**
     * @var TestConnectionServiceInterface
     */
    private $testConnectionService;

    public function __construct(
        WorldlineConfig $worldlineConfig,
        RequestInterface $request,
        TestConnectionServiceInterface $testConnectionService
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->request = $request;
        $this->testConnectionService = $testConnectionService;
    }

    public function test(): string
    {
        $this->worldlineConfig->setProductionModeFlag((bool) $this->getEnvMode());
        $this->worldlineConfig->setApiEndpoint($this->getEndpoint());
        $this->worldlineConfig->setMerchantId($this->getMerchantId());
        $this->worldlineConfig->setApiKey($this->getApiKey());
        $this->worldlineConfig->setApiSecret($this->getApiSecret());

        return $this->testConnectionService->execute();
    }

    private function getEnvMode(): int
    {
        if (null === $this->envMode) {
            $this->envMode = (int) $this->request->getParam(self::ENV_MODE);
        }

        return $this->envMode;
    }

    private function getEndpoint(): string
    {
        $endpoint = (string) $this->request->getParam(self::API_ENDPOINT[$this->getEnvMode()]);
        if ($endpoint) {
            return $endpoint;
        }

        return $this->worldlineConfig->getApiEndpoint(...$this->getScope());
    }

    private function getMerchantId(): string
    {
        $merchantId = (string) $this->request->getParam(self::MERCHANT_ID[$this->getEnvMode()]);
        if ($merchantId) {
            return $merchantId;
        }

        return $this->worldlineConfig->getMerchantId(...$this->getScope());
    }

    private function getApiKey(): string
    {
        $apiKey = trim((string) $this->request->getParam(self::API_KEY[$this->getEnvMode()]));
        if ($apiKey) {
            return $apiKey;
        }

        return $this->worldlineConfig->getApiKey(...$this->getScope());
    }

    private function getApiSecret(): string
    {
        $apiSecretKey = trim((string) $this->request->getParam(self::API_SECRET[$this->getEnvMode()]));
        if ($apiSecretKey && !$this->isObscured($apiSecretKey)) {
            return $apiSecretKey;
        }

        return $this->worldlineConfig->getApiSecret(...$this->getScope());
    }

    private function isObscured(string $value): bool
    {
        return (bool) preg_match('/\*\*\*\*\*\*/', $value);
    }

    private function getScope(): array
    {
        return ($this->request->getParam(ScopeInterface::SCOPE_WEBSITE) !== null)
            ? [(int) $this->request->getParam(ScopeInterface::SCOPE_WEBSITE)]
            : [0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT];
    }
}
