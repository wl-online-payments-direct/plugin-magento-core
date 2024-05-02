<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\QuotePayment\ResourceModel\QuotePayment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\QuotePayment\QuotePayment as QuotePaymentModel;
use Worldline\PaymentCore\Model\QuotePayment\ResourceModel\QuotePayment as QuotePaymentResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(QuotePaymentModel::class, QuotePaymentResource::class);
    }
}
