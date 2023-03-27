<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParamsFactory;
use Worldline\PaymentCore\Api\Service\GetPaymentProductsRequestBuilderInterface;

class GetPaymentProductsRequestBuilder implements GetPaymentProductsRequestBuilderInterface
{
    public const GET_PAYMENT_PRODUCTS_PARAMS = 'get_payment_product_params';

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetPaymentProductsParamsFactory
     */
    private $getPaymentProductsParamsFactory;

    public function __construct(
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        GetPaymentProductsParamsFactory $getPaymentProductsParamsFactory
    ) {
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->getPaymentProductsParamsFactory = $getPaymentProductsParamsFactory;
    }

    public function build(?int $storeId = null): GetPaymentProductsParams
    {
        /** @var GetPaymentProductsParams $getPaymentProductsParams */
        $getPaymentProductsParams = $this->getPaymentProductsParamsFactory->create();

        $currencyCode = $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
        $locale = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
        $countryCode = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE, $storeId);

        $getPaymentProductsParams->setLocale($locale);
        $getPaymentProductsParams->setCountryCode($countryCode);
        $getPaymentProductsParams->setCurrencyCode($currencyCode);

        $args = [self::GET_PAYMENT_PRODUCTS_PARAMS => $getPaymentProductsParams];
        $this->eventManager->dispatch('worldline_core_get_payment_product_params_builder', $args);

        return $getPaymentProductsParams;
    }
}
