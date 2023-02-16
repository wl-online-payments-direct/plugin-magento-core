<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\ValidatorPool;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceContext;

/**
 * Validate if acquired amount is valid for placing order
 */
class AcquiredAmountValidator implements PlaceOrderValidatorInterface
{
    /**
     * @var GetPaymentDetailsServiceInterface
     */
    private $getPaymentDetailsService;

    public function __construct(GetPaymentDetailsServiceInterface $getPaymentDetailsService)
    {
        $this->getPaymentDetailsService = $getPaymentDetailsService;
    }

    public function validate(CanPlaceContext $context): void
    {
        $paymentId = ((int)$context->getWorldlinePaymentId() . '_0');

        $response = $this->getPaymentDetailsService->execute($paymentId, (int) $context->getStoreId());
        $paymentOutput = $response->getPaymentOutput();
        if (!$paymentOutput->getAcquiredAmount()) {
            return;
        }

        if ($paymentOutput->getAcquiredAmount()->getAmount() !== $paymentOutput->getAmountOfMoney()->getAmount()) {
            throw new LocalizedException(__('Acquired amount is not reached'));
        }
    }
}
