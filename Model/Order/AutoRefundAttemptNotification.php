<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Model\Config\AutoRefundConfigProvider;
use Worldline\PaymentCore\Model\EmailSender;

class AutoRefundAttemptNotification
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var AutoRefundConfigProvider
     */
    private $autoRefundConfigProvider;

    public function __construct(
        EmailSender $emailSender,
        SenderResolverInterface $senderResolver,
        DateTime $dateTime,
        AutoRefundConfigProvider $autoRefundConfigProvider
    ) {
        $this->emailSender = $emailSender;
        $this->senderResolver = $senderResolver;
        $this->dateTime = $dateTime;
        $this->autoRefundConfigProvider = $autoRefundConfigProvider;
    }

    /**
     * @param CartInterface $quote
     * @return void
     * @throws MailException
     */
    public function notify(CartInterface $quote): void
    {
        $recipient = $this->senderResolver->resolve($this->autoRefundConfigProvider->getRecipient());
        $this->emailSender->sendEmail(
            $this->autoRefundConfigProvider->getEmailTemplate(),
            0,
            $this->autoRefundConfigProvider->getSender(),
            $recipient['email'] ?? '',
            $this->autoRefundConfigProvider->getEmailCopyTo(),
            $this->getVariables($quote),
            ['area' => Area::AREA_ADMINHTML, 'store' => 0]
        );
    }

    private function getVariables(CartInterface $quote): array
    {
        return [
            'store_id' => $quote->getStoreId(),
            'reserved_order_id' => $quote->getReservedOrderId(),
            'wl_payment_id' => $quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID),
            'customer_email' => $quote->getCustomerEmail(),
            'date' => date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp()),
        ];
    }
}
