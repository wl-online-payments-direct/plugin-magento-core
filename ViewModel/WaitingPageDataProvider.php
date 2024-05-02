<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\ViewModel;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;

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
     * @var QuoteResourceInterface
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

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingRepository;

    public function __construct(
        Product $productHelper,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        QuoteResourceInterface $quoteResource,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Image $imageHelper,
        SurchargingQuoteRepositoryInterface $surchargingRepository
    ) {
        $this->productHelper = $productHelper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->quoteResource = $quoteResource;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->imageHelper = $imageHelper;
        $this->surchargingRepository = $surchargingRepository;
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

    public function pendingPageUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'worldline/returns/pendingPayment',
            ['incrementId' => $this->getIncrementId()]
        );
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

    public function getQuote(): ?CartInterface
    {
        return $this->quoteResource->getQuoteByReservedOrderId($this->getIncrementId());
    }

    /**
     * @param ModelProduct $product
     * @return bool|string
     */
    public function getSmallImageUrl(ModelProduct $product)
    {
        return $this->productHelper->getSmallImageUrl($product);
    }

    public function getResizedImageUrl(ModelProduct $product): string
    {
        return $this->imageHelper->init($product, 'product_page_image_small')
            ->setImageFile($product->getSmallImage())
            ->resize(75, 75)
            ->getUrl();
    }

    public function convertAndFormatPrice(float $price): string
    {
        return $this->priceCurrency->convertAndFormat($price);
    }

    public function formatPrice(float $price): string
    {
        return $this->priceCurrency->format($price);
    }

    public function getSurchargeAmount(): float
    {
        $quote = $this->getQuote();
        if (!$quote) {
            return 0.0;
        }

        $quoteId = (int)$quote->getId();
        $surcharging = $this->surchargingRepository->getByQuoteId($quoteId);
        $paymentMethod = str_replace('_vault', '', (string)$quote->getPayment()->getMethod());
        if ($paymentMethod !== $surcharging->getPaymentMethod()) {
            return 0.0;
        }

        return (float)$surcharging->getAmount();
    }
}
