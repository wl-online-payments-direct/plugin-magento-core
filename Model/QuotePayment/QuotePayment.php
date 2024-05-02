<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\QuotePayment;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\QuotePaymentInterface;
use Worldline\PaymentCore\Model\QuotePayment\ResourceModel\QuotePayment as QuotePaymentResource;

class QuotePayment extends AbstractModel implements QuotePaymentInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'worldline_quote_payment_information';

    protected function _construct(): void
    {
        $this->_init(QuotePaymentResource::class);
    }
}
