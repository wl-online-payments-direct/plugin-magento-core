<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Model\Payment\ResourceModel\Payment as PaymentResource;

class Payment extends AbstractModel implements PaymentInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'worldline_payment';

    protected function _construct(): void
    {
        $this->_init(PaymentResource::class);
    }
}
