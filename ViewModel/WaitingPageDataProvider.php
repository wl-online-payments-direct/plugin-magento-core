<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\ViewModel;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;

class WaitingPageDataProvider implements ArgumentInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
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
}
