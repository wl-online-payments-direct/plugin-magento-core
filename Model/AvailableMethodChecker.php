<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Payment\Gateway\Config\Config as PaymentGatewayConfig;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\AvailableMethodCheckerInterface;

class AvailableMethodChecker implements AvailableMethodCheckerInterface
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(HttpContext $httpContext)
    {
        $this->httpContext = $httpContext;
    }

    /**
     * @param PaymentGatewayConfig $config
     * @param CartInterface $quote
     * @return bool
     */
    public function checkIsAvailable(PaymentGatewayConfig $config, CartInterface $quote): bool
    {
        if (!$this->customerGroupValidation($config, $quote)) {
            return false;
        }
        return true;
    }

    /**
     * @param PaymentGatewayConfig $config
     * @param CartInterface $quote
     * @return bool
     */
    private function customerGroupValidation(PaymentGatewayConfig $config, CartInterface $quote): bool
    {
        $isValid = true;
        if ((int) $config->getValue('allow_specific_customer_group') === 1) {
            if ($config->getValue('customer_group') === null) {
                return false;
            }

            $availableCustomerGroups = array_map('intval', explode(
                ',',
                (string)$config->getValue('customer_group')
            ));
            $currentCustomerGroup = $this->getCustomerGroup($quote);
            if (!in_array($currentCustomerGroup, $availableCustomerGroups, true)
                && !in_array(32000, $availableCustomerGroups, true) // ALL GROUPS
            ) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * @param CartInterface $quote
     * @return int|null
     */
    private function getCustomerGroup(CartInterface $quote): ?int
    {
        /** @var Customer $customer */
        $customer = $quote->getCustomer();
        return (int) $customer->getGroupId() ?: $this->httpContext->getValue(Context::CONTEXT_GROUP);
    }
}
