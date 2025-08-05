<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Magento\Catalog\Block;

use Worldline\HostedCheckout\Model\Config\Source\MealvouchersProductTypes;

class ProductView
{
    public function afterGetJsonConfig(\Magento\Catalog\Block\Product\View $subject, $result)
    {
        $product = $subject->getProduct();
        $config = json_decode($result, true);
        $productType = $product->getData('worldline_mealvouchers_product_type');
        $config['eligible_for_meal_vouchers'] = $this->isProductTypeValid($productType);
        $config['product_type'] = $this->isProductTypeValid($productType) ?
            $product->getData('worldline_mealvouchers_product_type') : '';

        return json_encode($config);
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
