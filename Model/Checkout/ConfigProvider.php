<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const WL_CONFIG_KEY = 'worldlineCheckoutConfig';

    /**
     * @var MethodList
     */
    private $methodList;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    /**
     * @var string[]
     */
    private $allowedPaymentMethods;

    public function __construct(
        MethodList $methodList,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        GeneralSettingsConfigInterface $generalSettings,
        array $allowedPaymentMethods = []
    ) {
        $this->methodList = $methodList;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->generalSettings = $generalSettings;
        $this->allowedPaymentMethods = $allowedPaymentMethods;
    }

    public function getConfig(): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $quote = $this->checkoutSession->getQuote();
        if (!$this->generalSettings->isApplySurcharge($storeId)
            || $this->isPaymentsUnavailable($quote)
            || (float)$quote->getGrandTotal() < 0.00001
        ) {
            return [];
        }

        return [
            self::WL_CONFIG_KEY => [
                'surchargeMessage' => __('Please note that a surcharge may be added to the amount you have to pay ' .
                    'depending on the payment method you have chosen.')->render()
            ]
        ];
    }

    private function isPaymentsUnavailable(CartInterface $quote): bool
    {
        $payments = $this->methodList->getAvailableMethods($quote);
        foreach ($payments as $payment) {
            if (in_array($payment->getCode(), $this->allowedPaymentMethods, true)) {
                return false;
            }
        }

        return true;
    }
}
