<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\CreateRequest;

use InvalidArgumentException;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\RedirectionData;
use OnlinePayments\Sdk\Domain\RedirectionDataFactory;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use OnlinePayments\Sdk\Domain\ThreeDSecureFactory;
use Worldline\PaymentCore\Api\Service\CreateRequest\ThreeDSecureDataBuilderInterface;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
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
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    /**
     * @var string[]
     */
    private $returnUrls;

    public function __construct(
        ThreeDSecureFactory $threeDSecureFactory,
        RedirectionDataFactory $redirectionDataFactory,
        MethodNameExtractor $methodNameExtractor,
        ParamsHandler $threeDSecureParamsHandler,
        GeneralSettingsConfigInterface $generalSettings,
        array $returnUrls = []
    ) {
        $this->threeDSecureFactory = $threeDSecureFactory;
        $this->redirectionDataFactory = $redirectionDataFactory;
        $this->methodNameExtractor = $methodNameExtractor;
        $this->threeDSecureParamsHandler = $threeDSecureParamsHandler;
        $this->generalSettings = $generalSettings;
        $this->returnUrls = $returnUrls;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(CartInterface $quote, $isCreditCardPayment = false): ThreeDSecure
    {
        $methodCode = $this->methodNameExtractor->extract($quote->getPayment());
        $returnUrl = $this->returnUrls[$methodCode] ?? '';

        $storeId = (int)$quote->getStoreId();
        $threeDSecure = $this->threeDSecureFactory->create();

        $this->threeDSecureParamsHandler->handle($threeDSecure, (float)$quote->getGrandTotal(), $storeId);

        if ($isCreditCardPayment && $this->generalSettings->isThreeDEnabled($storeId)) {
            $threeDSecure->setRedirectionData($this->getRedirectionData($returnUrl, $storeId));
        }

        return $threeDSecure;
    }

    private function getRedirectionData(string $returnUrl, int $storeId): RedirectionData
    {
        $redirectionData = $this->redirectionDataFactory->create();
        $redirectionData->setReturnUrl($this->generalSettings->getReturnUrl($returnUrl, $storeId));

        return $redirectionData;
    }
}
