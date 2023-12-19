<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\ValidatorPool;

use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\Service\GetPaymentDetailsServiceInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceContext;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

/**
 * Validate if acquired amount is valid for placing order
 */
class AcquiredAmountValidator implements PlaceOrderValidatorInterface
{
    /**
     * @var GetPaymentDetailsServiceInterface
     */
    private $getPaymentDetailsService;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    public function __construct(
        GetPaymentDetailsServiceInterface $getPaymentDetailsService,
        PaymentIdFormatterInterface $paymentIdFormatter
    ) {
        $this->getPaymentDetailsService = $getPaymentDetailsService;
        $this->paymentIdFormatter = $paymentIdFormatter;
    }

    public function validate(CanPlaceContext $context): void
    {
        $wlPaymentId = $this->paymentIdFormatter->validateAndFormat(
            (string) $context->getWorldlinePaymentId(),
            true
        );

        $response = $this->getPaymentDetailsService->execute($wlPaymentId, (int) $context->getStoreId());
        $paymentOutput = $response->getPaymentOutput();
        if (!$paymentOutput->getAcquiredAmount()
            || $paymentOutput->getSurchargeSpecificOutput()
            || $paymentOutput->getSepaDirectDebitPaymentMethodSpecificOutput()
            || $response->getStatusOutput()->getStatusCode() === TransactionStatusInterface::CAPTURE_REQUESTED
        ) {
            return;
        }

        if ($paymentOutput->getAcquiredAmount()->getAmount() !== $paymentOutput->getAmountOfMoney()->getAmount()) {
            throw new LocalizedException(__('Acquired amount is not reached'));
        }
    }
}
