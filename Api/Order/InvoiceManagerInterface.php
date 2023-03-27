<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Order;

use Magento\Sales\Api\Data\OrderInterface;

interface InvoiceManagerInterface
{
    public function createInvoice(OrderInterface $order): void;
}
