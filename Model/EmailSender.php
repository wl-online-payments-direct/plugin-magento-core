<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\EmailSendingListInterface;
use Worldline\PaymentCore\Api\EmailSendingListRepositoryInterface;
use Worldline\PaymentCore\Model\Config\OrderSynchronizationConfig;

class EmailSender
{
    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSynchronizationConfig
     */
    private $orderSynchronizationConfig;

    /**
     * @var MessageInterfaceFactory
     */
    private $messageFactory;

    /**
     * @var TransportInterfaceFactory
     */
    private $mailTransportFactory;

    /**
     * @var EmailSendingListRepositoryInterface
     */
    private $emailSendingListRepository;

    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        OrderSynchronizationConfig $orderSynchronizationConfig,
        MessageInterfaceFactory $messageFactory,
        TransportInterfaceFactory $mailTransportFactory,
        EmailSendingListRepositoryInterface $emailSendingListRepository
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->orderSynchronizationConfig = $orderSynchronizationConfig;
        $this->messageFactory = $messageFactory;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->emailSendingListRepository = $emailSendingListRepository;
    }

    public function sendPaymentRefusedEmail(CartInterface $quote): bool
    {
        $storeId = (int)$quote->getStoreId();
        if (!$this->orderSynchronizationConfig->isPaymentRefusedEmailsEnabled($storeId)) {
            return false;
        }

        $incrementId = (string)$quote->getReservedOrderId();

        if ($this->emailSendingListRepository->count($incrementId, EmailSendingListInterface::PAYMENT_REFUSED) > 0) {
            return false;
        }

        $sendTo = $quote->getCustomerEmail();
        if (!$sendTo) {
            return false;
        }

        $sendFrom = $this->orderSynchronizationConfig->getRefusedPaymentSender($storeId);
        $emailTemplate = $this->orderSynchronizationConfig->getRefusedPaymentTemplate($storeId);

        $this->sendEmail($emailTemplate, $storeId, $sendFrom, $sendTo);

        $this->emailSendingListRepository->setQuoteToEmailList(
            $incrementId,
            EmailSendingListInterface::PAYMENT_REFUSED
        );

        return true;
    }

    public function sendEmail(
        string $template,
        int $storeId,
        string $sendFrom,
        string $sendTo,
        string $ccTo = '',
        array $vars = [],
        array $options = []
    ): bool {
        if (!$options) {
            $options = ['area' => Area::AREA_FRONTEND, 'store' => $storeId];
        }

        $addTo = $this->emailsStringToArray($sendTo);

        if ($ccTo) {
            $addTo[] = $ccTo;
        }

        try {
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions($options)
                ->setTemplateVars($vars)
                ->setFromByScope($sendFrom, $storeId)
                ->addTo($addTo)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $this->inlineTranslation->resume();

            return false;
        }
    }

    public function sendEmailWithoutTemplate(string $body, string $from, string $userName, string $addTo): void
    {
        try {
            $message = $this->messageFactory->create();
            $message->setFromAddress($from, $userName);
            $message->addTo($addTo);
            $message->setSubject(__('New feature suggestion for Magento'));
            $message->setBodyHtml($body);
            $transport = $this->mailTransportFactory->create(['message' => $message]);
            $transport->sendMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }


    /**
     * Converts a string of emails to array
     *
     * @param string $emails
     *
     * @return string[]
     */
    private function emailsStringToArray(string $emails): array
    {
        $emailsCleaned = str_replace(' ', '', $emails);
        $emailsArray = explode(',', $emailsCleaned);

        return array_filter($emailsArray, function ($email) { return $email !== ''; });
    }
}
