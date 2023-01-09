<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\CreateRequest\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Customer;

interface CustomerDataBuilderInterface
{
    public function build(CartInterface $quote): Customer;
}
