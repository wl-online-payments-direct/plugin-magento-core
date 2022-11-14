<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\ViewModel;

use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;

class WaitingPageDataProvider implements ArgumentInterface
{
    /**
     * @var Product
     */
    private $productHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Product $productHelper,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        QuoteResource $quoteResource,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productHelper = $productHelper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->quoteResource = $quoteResource;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }

    public function getNotificationMessage(): Phrase
    {
        return __('Please wait, the payment is being processed...');
    }

    public function checkOrderUrl(): string
    {
        return $this->urlBuilder->getUrl('worldline/returns/checkOrder');
    }

    public function successUrl(): string
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success');
    }

    public function failUrl(): string
    {
        return $this->urlBuilder->getUrl('worldline/returns/failed');
    }

    public function pendingOrderUrl(): string
    {
        return $this->urlBuilder->getUrl('worldline/returns/pendingOrder');
    }

    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
    }

    public function getIncrementId(): string
    {
        return $this->request->getParam('incrementId', '');
    }

    public function getQuote(): CartInterface
    {
        $incrementId = $this->getIncrementId();

        return $this->quoteResource->getQuoteByReservedOrderId($incrementId);
    }

    /**
     * @param ModelProduct $product
     * @return bool|string
     */
    public function getSmallImageUrl(ModelProduct $product)
    {
        return $this->productHelper->getSmallImageUrl($product);
    }

    public function convertAndFormatPrice(float $price): string
    {
        return $this->priceCurrency->convertAndFormat($price);
    }
}
