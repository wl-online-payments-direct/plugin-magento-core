<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;
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

    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        OrderSynchronizationConfig $orderSynchronizationConfig
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->orderSynchronizationConfig = $orderSynchronizationConfig;
    }

    public function sendPaymentRefusedEmail(CartInterface $quote): bool
    {
        $storeId = (int)$quote->getStoreId();
        $sendTo = $quote->getCustomerEmail();
        if (!$sendTo) {
            return false;
        }

        $sendFrom = $this->orderSynchronizationConfig->getRefusedPaymentSender($storeId);
        $emailTemplate = $this->orderSynchronizationConfig->getRefusedPaymentTemplate($storeId);

        return $this->sendEmail($emailTemplate, $storeId, $sendFrom, $sendTo);
    }

    public function sendEmail(string $template, int $storeId, $sendFrom, $sendTo, array $vars = []): bool
    {
        try {
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($vars)
                ->setFromByScope($sendFrom, $storeId)
                ->addTo($sendTo)
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
}
