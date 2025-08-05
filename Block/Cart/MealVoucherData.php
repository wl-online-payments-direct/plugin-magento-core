<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;
use Magento\Framework\Registry;
use Worldline\HostedCheckout\Model\Config\Source\MealvouchersProductTypes;

class MealVoucherData extends Template
{
    /** @var Session $checkoutSession */
    private $checkoutSession;
    /** @var Registry $registry */
    private $registry;

    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
    }

    public function getProductMealVoucherData(): array
    {
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return ['eligible' => false, 'type' => ''];
        }

        $productType = $product->getData('worldline_mealvouchers_product_type');
        $isValid = $this->isProductTypeValid($productType);

        return [
            'eligible' => $isValid,
            'type' => $isValid ? $productType : '',
        ];
    }

    public function getEligibleCartItems(): array
    {
        $eligibleItems = [];

        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productType = $product->getData('worldline_mealvouchers_product_type');

            if ($this->isProductTypeValid($productType)) {
                $eligibleItems[] = [
                    'item_id' => $item->getId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'product_type' => $productType,
                ];
            }
        }

        return $eligibleItems;
    }

    private function isProductTypeValid($productType)
    {
        return in_array($productType, [
            MealvouchersProductTypes::FOOD_AND_DRINK,
            MealvouchersProductTypes::HOME_AND_GARDEN,
            MealvouchersProductTypes::GIFT_AND_FLOWERS
        ]);
    }
}
