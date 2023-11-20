<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\App\Area;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Model\Config\AutoRefundConfigProvider;
use Worldline\PaymentCore\Model\EmailSender;

class AutoRefundToCustomerNotification
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var AutoRefundConfigProvider
     */
    private $autoRefundConfigProvider;

    public function __construct(
        EmailSender $emailSender,
        AutoRefundConfigProvider $autoRefundConfigProvider
    ) {
        $this->emailSender = $emailSender;
        $this->autoRefundConfigProvider = $autoRefundConfigProvider;
    }

    /**
     * @param CartInterface $quote
     * @return void
     */
    public function notify(CartInterface $quote): void
    {
        $storeId = (int)$quote->getStoreId();
        if (!$this->autoRefundConfigProvider->isEnabledToCustomer($storeId)) {
            return;
        }

        $this->emailSender->sendEmail(
            $this->autoRefundConfigProvider->getEmailTemplateToCustomer($storeId),
            $storeId,
            $this->autoRefundConfigProvider->getSenderToCustomer($storeId),
            $quote->getCustomerEmail(),
            '',
            $this->getVariables($quote),
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        );
    }

    private function getVariables(CartInterface $quote): array
    {
        $billingAddress = $quote->getBillingAddress();
        $customerName = $billingAddress ? $billingAddress->getFirstname() : 'Client';

        return [
            'store_id' => $quote->getStoreId(),
            'reserved_order_id' => $quote->getReservedOrderId(),
            'customer_name' => $customerName,
            'customer_email' => $quote->getCustomerEmail()
        ];
    }
}
