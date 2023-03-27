<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingQuote;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\SurchargingQuoteInterface;

class SurchargingQuote extends AbstractModel implements SurchargingQuoteInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'worldline_surcharging_quote';

    protected function _construct(): void
    {
        $this->_init(ResourceModel\SurchargingQuote::class);
    }
}
