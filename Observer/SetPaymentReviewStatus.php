<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
use Worldline\PaymentCore\Model\Order\ValidatorPool\DiscrepancyValidator;

class SetPaymentReviewStatus implements ObserverInterface
{
    /**
     * @var DiscrepancyValidator
     */
    private $discrepancyValidator;

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    public function __construct(
        DiscrepancyValidator $discrepancyValidator,
        GeneralSettingsConfigInterface $generalSettings
    ) {
        $this->discrepancyValidator = $discrepancyValidator;
        $this->generalSettings = $generalSettings;
    }

    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if ($payment instanceof Payment && (strpos($payment->getMethod(), 'worldline') !== 0)) {
            return;
        }

        if (!$this->generalSettings->isAmountDiscrepancyEnabled()) {
            return;
        }

        if ($this->isOrderWithDiscrepancy($order)) {
            $payment->setIsTransactionPending(true);
        }
    }

    private function isOrderWithDiscrepancy(Order $order): bool
    {
        return $this->discrepancyValidator->compareAmounts($order->getGrandTotal(), $order->getIncrementId());
    }
}
