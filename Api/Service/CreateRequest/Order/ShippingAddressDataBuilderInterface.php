<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Shipping;

interface ShippingAddressDataBuilderInterface
{
    public function build(CartInterface $quote): Shipping;

    public function buildShippingAddress(CartInterface $quote, Shipping $shipping): void;

    public function buildShippingCost(CartInterface $quote, Shipping $shipping): void;
}
