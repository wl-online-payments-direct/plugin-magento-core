<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingQuote\ResourceModel\SurchargingQuote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\SurchargingQuote\SurchargingQuote;
use Worldline\PaymentCore\Model\SurchargingQuote\ResourceModel\SurchargingQuote as SurchargingQuoteResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(SurchargingQuote::class, SurchargingQuoteResource::class);
    }
}
