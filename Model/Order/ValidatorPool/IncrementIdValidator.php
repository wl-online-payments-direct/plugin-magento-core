<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\ValidatorPool;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderIncrementIdChecker;
use Worldline\PaymentCore\Model\Order\CanPlaceContext;

/**
 * Validate if an order has been created with this increment ID
 */
class IncrementIdValidator implements PlaceOrderValidatorInterface
{
    /**
     * @var OrderIncrementIdChecker
     */
    private $orderIncrementIdChecker;

    public function __construct(OrderIncrementIdChecker $orderIncrementIdChecker)
    {
        $this->orderIncrementIdChecker = $orderIncrementIdChecker;
    }

    public function validate(CanPlaceContext $context): void
    {
        if (!$context->getIncrementId()) {
            return;
        }

        if ($this->orderIncrementIdChecker->isIncrementIdUsed($context->getIncrementId())) {
            throw new LocalizedException(__('Increment ID has been used'));
        }
    }
}
