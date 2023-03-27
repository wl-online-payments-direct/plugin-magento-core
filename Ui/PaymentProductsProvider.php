<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Ui;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterface;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterfaceFactory;
use Worldline\PaymentCore\Api\Service\GetPaymentProductsServiceInterface;
use Worldline\PaymentCore\Api\Ui\PaymentProductsProviderInterface;
use Worldline\PaymentCore\Service\Payment\GetPaymentProductsRequestBuilder;

class PaymentProductsProvider implements PaymentProductsProviderInterface
{
    public const CACHE_ID = "worldline_payment_products";
    public const CACHE_LIFETIME = 86400; //24h

    public const GENERATE_CACHE_ID_EVENT = 'worldline_core_payment_products_cache_id_generate';

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
     * @var GetPaymentProductsServiceInterface
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
        GetPaymentProductsServiceInterface $getPaymentProductsRequest,
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
            $response = $this->getPaymentProductsRequest->execute($paymentProductsQueryParams, $storeId);
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
