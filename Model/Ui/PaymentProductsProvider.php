<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Ui;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterface;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterfaceFactory;
use Worldline\PaymentCore\Api\Service\GetPaymentProductsRequestInterface;
use Worldline\PaymentCore\Service\Payment\GetPaymentProductsRequestBuilder;

class PaymentProductsProvider
{
    public const CACHE_ID = "worldline_payment_products";
    public const CACHE_LIFETIME = 86400; //24h

    public const GENERATE_CACHE_ID_EVENT = 'worldline_core_payment_products_cache_id_generate';

    /**
     * @link https://support.direct.ingenico.com/en/payment-methods/view-by-payment-product/
     */
    public const PAYMENT_GROUP_MOBILE = 'Mobile';
    public const PAYMENT_GROUP_CARD = 'Cards (debit & credit)';
    public const PAYMENT_GROUP_E_WALLET = 'e-Wallet';
    public const PAYMENT_GROUP_CONSUMER_CREDIT = 'Consumer Credit';
    public const PAYMENT_GROUP_REALTIME_BANKING = 'Real-time banking';
    public const PAYMENT_GROUP_GIFT_CARD = 'Gift card';
    public const PAYMENT_GROUP_INSTALMENT = 'Instalment';
    public const PAYMENT_GROUP_PREPAID = 'Prepaid';
    public const PAYMENT_GROUP_POSTPAID = 'Postpaid';
    public const PAYMENT_GROUP_DIRECT_DEBIT = 'Direct Debit';

    public const PAYMENT_PRODUCTS = [
        1    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Visa'],
        2    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'American Express'],
        3    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Mastercard'],
        117  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Maestro'],
        125  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'JCB'],
        130  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Carte Bancaire'],
        132  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Diners Club'],
        302  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Apple Pay'],
        320  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Google Pay'],
        771  => ['group' => self::PAYMENT_GROUP_DIRECT_DEBIT,     'label' => 'SEPA Direct Debit'],
        809  => ['group' => self::PAYMENT_GROUP_REALTIME_BANKING, 'label' => 'iDEAL'],
        840  => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Paypal'],
        861  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Alipay'],
        863  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'WeChat Pay'],
        3012 => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Bancontact'],
        3112 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Illicado'],
        3301 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Pay Now'],
        3302 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Pay Later'],
        3303 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Financing'],
        3304 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Bank Transfer'],
        3305 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Direct Debit'],
        5001 => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Bizum'],
        5100 => ['group' => self::PAYMENT_GROUP_CONSUMER_CREDIT,  'label' => 'Cpay'],
        5110 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney 3x-4x'],
        5125 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney Financement Long'],
        5402 => ['group' => self::PAYMENT_GROUP_PREPAID,          'label' => 'Mealvouchers'],
        5500 => ['group' => self::PAYMENT_GROUP_POSTPAID,         'label' => 'Multibanco'],
        5600 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'OneyBrandedGiftCard'],
        5700 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Intersolve']
    ];

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheIdentifierInterfaceFactory
     */
    private $cacheIdentifierFactory;

    /**
     * @var GetPaymentProductsRequestInterface
     */
    private $getPaymentProductsRequest;

    /**
     * @var GetPaymentProductsRequestBuilder
     */
    private $getPaymentProductsRequestBuilder;

    public function __construct(
        CacheInterface $cache,
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        SerializerInterface $serializer,
        CacheIdentifierInterfaceFactory $cacheIdentifierFactory,
        GetPaymentProductsRequestInterface $getPaymentProductsRequest,
        GetPaymentProductsRequestBuilder $getPaymentProductsRequestBuilder
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->serializer = $serializer;
        $this->cacheIdentifierFactory = $cacheIdentifierFactory;
        $this->getPaymentProductsRequest = $getPaymentProductsRequest;
        $this->getPaymentProductsRequestBuilder = $getPaymentProductsRequestBuilder;
    }

    public function getPaymentProducts(int $storeId): array
    {
        $cachedPayProducts = $this->getPaymentProductsFromCache($storeId);
        if (!empty($cachedPayProducts)) {
            return $cachedPayProducts;
        }

        try {
            $paymentProductsQueryParams = $this->getPaymentProductsRequestBuilder->build($storeId);
            $response = $this->getPaymentProductsRequest->get($paymentProductsQueryParams, $storeId);
            $paymentProducts = $response->getPaymentProducts();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }

        if (empty($paymentProducts)) {
            return [];
        }

        $formattedPayProducts = $this->formatPayProducts($paymentProducts);
        $this->savePaymentProductsToCache($formattedPayProducts, $storeId);

        return $formattedPayProducts;
    }

    public function savePaymentProductsToCache(array $paymentProducts, int $storeId): void
    {
        $this->cache->save(
            $this->serializer->serialize($paymentProducts),
            $this->generateCacheIdentifier($storeId),
            [],
            self::CACHE_LIFETIME
        );
    }

    public function getPaymentProductsFromCache(int $storeId): array
    {
        $paymentProducts = $this->cache->load($this->generateCacheIdentifier($storeId));
        if (!empty($paymentProducts)) {
            return $this->serializer->unserialize($paymentProducts);
        }

        return [];
    }

    public function generateCacheIdentifier(int $storeId): string
    {
        /** @var CacheIdentifierInterface $cacheIdentifier */
        $cacheIdentifier = $this->cacheIdentifierFactory->create();
        $cacheIdentifier->setCacheIdentifier(self::CACHE_ID . '_' . $storeId);

        $this->eventManager->dispatch(self::GENERATE_CACHE_ID_EVENT, ['cache_identifier' => $cacheIdentifier]);

        return $cacheIdentifier->getCacheIdentifier();
    }

    private function formatPayProducts(array $paymentProducts): array
    {
        $resultPayProducts = [];
        foreach ($paymentProducts as $pP) {
            $resultPayProducts[$pP->getId()] = [
                'method' => $pP->getPaymentMethod(),
                'label' => $pP->getDisplayHints()->getLabel()
            ];
        }

        return $resultPayProducts;
    }
}
