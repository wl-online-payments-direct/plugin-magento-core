<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\EmailSendingListRepositoryInterface;
use Worldline\PaymentCore\Model\Config\OrderNotificationConfigProvider;
use Worldline\PaymentCore\Model\EmailSender;
use Worldline\PaymentCore\Api\QuoteResourceInterface;

class FailedOrderCreationNotification
{
    public const WEBHOOK_SPACE = 'webhook';
    public const WAITING_PAGE_SPACE = 'waiting page';
    public const WAITING_CRON_SPACE = 'cron';

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var OrderNotificationConfigProvider
     */
    private $orderNotificationConfigProvider;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var EmailSendingListRepositoryInterface
     */
    private $emailSendingListRepository;

    public function __construct(
        EmailSender $emailSender,
        OrderNotificationConfigProvider $orderNotificationConfigProvider,
        SenderResolverInterface $senderResolver,
        DateTime $dateTime,
        QuoteResourceInterface $quoteResource,
        EmailSendingListRepositoryInterface $emailSendingListRepository
    ) {
        $this->emailSender = $emailSender;
        $this->orderNotificationConfigProvider = $orderNotificationConfigProvider;
        $this->senderResolver = $senderResolver;
        $this->dateTime = $dateTime;
        $this->quoteResource = $quoteResource;
        $this->emailSendingListRepository = $emailSendingListRepository;
    }

    /**
     * @param string $incrementId
     * @param string $errorMessage
     * @param string $space
     * @return void
     * @throws MailException
     */
    public function notify(string $incrementId, string $errorMessage, string $space): void
    {
        if (!$this->orderNotificationConfigProvider->isEnabled()) {
            return;
        }

        if ($this->emailSendingListRepository->count($incrementId, EmailSendingListInterface::FAILED_ORDER) > 0) {
            return;
        }

        $recipient = $this->senderResolver->resolve($this->orderNotificationConfigProvider->getRecipient());
        $this->emailSender->sendEmail(
            $this->orderNotificationConfigProvider->getEmailTemplate(),
            0,
            $this->orderNotificationConfigProvider->getSender(),
            $recipient['email'] ?? '',
            $this->orderNotificationConfigProvider->getEmailCopyTo(),
            $this->getVariables($incrementId, $errorMessage, $space),
            ['area' => Area::AREA_ADMINHTML, 'store' => 0]
        );

        $this->emailSendingListRepository->setQuoteToEmailList(
            $incrementId,
            EmailSendingListInterface::FAILED_ORDER
        );
    }

    private function getVariables(string $incrementId, string $errorMessage, string $space): array
    {
        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote) {
            return [];
        }

        return [
            'store_id' => $quote->getStoreId(),
            'reserved_order_id' => $incrementId,
            'wl_payment_id' => $quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID),
            'customer_email' => $quote->getCustomerEmail(),
            'date' => date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp()),
            'error_message' => $errorMessage,
            'space' => $space,
        ];
    }
}
