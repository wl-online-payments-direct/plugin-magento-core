<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\AmountDiscrepancy;

use Worldline\PaymentCore\Model\Config\AmountDiscrepancyConfig;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Worldline\PaymentCore\Model\EmailSender;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Backend\Model\UrlInterface;

class AmountDiscrepancyNotification
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var AmountDiscrepancyConfig
     */
    private $amountDiscrepancyConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var CurrencyAmountNormalizer
     */
    private $currencyAmountNormalizer;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    public function __construct(
        EmailSender              $emailSender,
        AmountDiscrepancyConfig  $amountDiscrepancyConfig,
        SenderResolverInterface  $senderResolver,
        CurrencyAmountNormalizer $currencyAmountNormalizer,
        UrlInterface             $urlInterface
    ) {
        $this->emailSender = $emailSender;
        $this->amountDiscrepancyConfig = $amountDiscrepancyConfig;
        $this->senderResolver = $senderResolver;
        $this->currencyAmountNormalizer = $currencyAmountNormalizer;
        $this->urlInterface = $urlInterface;
    }

    public function notify(OrderInterface $order, $paidAmount): void
    {
        if (!$this->amountDiscrepancyConfig->isEnabled()) {
            return;
        }

        $recipient = $this->senderResolver->resolve($this->amountDiscrepancyConfig->getRecipient());
        $this->emailSender->sendEmail(
            $this->amountDiscrepancyConfig->getEmailTemplate(),
            0,
            $this->amountDiscrepancyConfig->getSender(),
            $recipient['email'] ?? '',
            $this->amountDiscrepancyConfig->getEmailCopyTo(),
            $this->getVariables($order, $paidAmount),
            ['area' => Area::AREA_ADMINHTML, 'store' => 0]
        );
    }

    /**
     * @param OrderInterface $order
     * @param string $paidAmount
     *
     * @return array
     */
    private function getVariables(OrderInterface $order, $paidAmount): array
    {
        $formattedPaidAmount = $this->currencyAmountNormalizer->normalize(
            (float)$paidAmount,
            $order->getOrderCurrencyCode()
        );

        return [
            'increment_id' => $order->getIncrementId(),
            'quote_id' => $order->getQuoteId(),
            'order_grand_total' => $order->getGrandTotal(),
            'payment_amount' => $formattedPaidAmount,
            'discrepancy_amount' => $order->getGrandTotal() - $formattedPaidAmount,
            'admin_order_url' => $this->urlInterface->getUrl('sales/order/view', ['order_id' => $order->getId()])
        ];
    }
}
