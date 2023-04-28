<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\ValidatorPool;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Model\Order\CanPlaceContext;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

/**
 * Validate if status code is valid for placing order
 */
class StatusCodeValidator implements PlaceOrderValidatorInterface
{
    public function validate(CanPlaceContext $context): void
    {
        $isValid = in_array(
            $context->getStatusCode(),
            [
                TransactionStatusInterface::PENDING_CAPTURE_CODE,
                TransactionStatusInterface::CAPTURED_CODE,
                TransactionStatusInterface::CAPTURE_REQUESTED,
            ],
            true
        );

        if (!$isValid) {
            throw new LocalizedException(__('Code %1 is not valid for order creation', $context->getStatusCode()));
        }
    }
}
