<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest;

use InvalidArgumentException;
use Magento\Payment\Gateway\Config\Config;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\RedirectionData;
use OnlinePayments\Sdk\Domain\RedirectionDataFactory;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use OnlinePayments\Sdk\Domain\ThreeDSecureFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\ThreeDSecureDataBuilderInterface;
use Worldline\PaymentCore\Model\MethodNameExtractor;
use Worldline\PaymentCore\Model\ThreeDSecure\ParamsHandler;

class ThreeDSecureDataBuilder implements ThreeDSecureDataBuilderInterface
{
    /**
     * @var ThreeDSecureFactory
     */
    private $threeDSecureFactory;

    /**
     * @var RedirectionDataFactory
     */
    private $redirectionDataFactory;

    /**
     * @var MethodNameExtractor
     */
    private $methodNameExtractor;

    /**
     * @var ParamsHandler
     */
    private $threeDSecureParamsHandler;

    /**
     * @var Config[]
     */
    private $configProviders;

    public function __construct(
        ThreeDSecureFactory $threeDSecureFactory,
        RedirectionDataFactory $redirectionDataFactory,
        MethodNameExtractor $methodNameExtractor,
        ParamsHandler $threeDSecureParamsHandler,
        array $configProviders = []
    ) {
        $this->threeDSecureFactory = $threeDSecureFactory;
        $this->redirectionDataFactory = $redirectionDataFactory;
        $this->methodNameExtractor = $methodNameExtractor;
        $this->threeDSecureParamsHandler = $threeDSecureParamsHandler;
        $this->configProviders = $configProviders;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(CartInterface $quote): ThreeDSecure
    {
        $methodCode = $this->methodNameExtractor->extract($quote->getPayment());
        $config = $this->configProviders[$methodCode] ?? null;
        if (!$config instanceof Config) {
            throw new InvalidArgumentException(sprintf('Config Provider must extends %s', Config::class));
        }

        $storeId = (int)$quote->getStoreId();
        $threeDSecure = $this->threeDSecureFactory->create();

        $isSkipAuthentication = $config->hasSkipAuthentication($storeId);
        if (!$isSkipAuthentication) {
            $this->threeDSecureParamsHandler->handle(
                $threeDSecure,
                (float)$quote->getBaseSubtotal(),
                $config->isThreeDExemptionEnabled($storeId),
                $config->isTriggerAnAuthentication($storeId)
            );
        }

        $threeDSecure->setSkipAuthentication($isSkipAuthentication);
        $threeDSecure->setRedirectionData($this->getRedirectionData($config, $storeId));

        return $threeDSecure;
    }

    private function getRedirectionData(Config $config, int $storeId): RedirectionData
    {
        $redirectionData = $this->redirectionDataFactory->create();
        $redirectionData->setReturnUrl($config->getReturnUrl($storeId));

        return $redirectionData;
    }
}
