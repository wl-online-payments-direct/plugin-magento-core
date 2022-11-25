<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Magento\Sales\Model\Order;

use Magento\Sales\Model\Order;
use Worldline\PaymentCore\Model\RefundRequest\RefundValidator;

class CheckRefundAbility
{
    /**
     * @var RefundValidator
     */
    private $refundValidator;

    public function __construct(RefundValidator $refundValidator)
    {
        $this->refundValidator = $refundValidator;
    }

    /**
     * @param Order $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanCreditmemo(Order $subject, bool $result): bool
    {
        if (!$result) {
            return $result;
        }

        if (!$this->refundValidator->canRefund($subject)) {
            return false;
        }

        return $result;
    }
}
