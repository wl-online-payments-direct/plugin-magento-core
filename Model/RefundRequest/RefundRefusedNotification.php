<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Model\Config\RefundRefusedConfig;
use Worldline\PaymentCore\Model\EmailSender;

class RefundRefusedNotification
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var RefundRefusedConfig
     */
    private $refundRefusedConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    public function __construct(
        EmailSender $emailSender,
        RefundRefusedConfig $refundRefusedConfig,
        SenderResolverInterface $senderResolver,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->emailSender = $emailSender;
        $this->refundRefusedConfig = $refundRefusedConfig;
        $this->senderResolver = $senderResolver;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    public function notify(CartInterface $quote, string $incrementId, int $creditmemoId): void
    {
        if (!$this->refundRefusedConfig->isEnabled()) {
            return;
        }

        $recipient = $this->senderResolver->resolve($this->refundRefusedConfig->getRecipient());
        $this->emailSender->sendEmail(
            $this->refundRefusedConfig->getEmailTemplate(),
            0,
            $this->refundRefusedConfig->getSender(),
            $recipient['email'] ?? '',
            $this->refundRefusedConfig->getEmailCopyTo(),
            $this->getVariables($quote, $incrementId, $creditmemoId),
            ['area' => Area::AREA_ADMINHTML, 'store' => 0]
        );
    }

    private function getVariables(CartInterface $quote, string $incrementId, int $creditmemoId): array
    {
        $creditmemoEntity = $this->creditmemoRepository->get($creditmemoId);

        return [
            'store_id' => $quote->getStoreId(),
            'order_id' => $incrementId,
            'creditmemo_id' => $creditmemoEntity->getIncrementId(),
            'wl_payment_id' => $quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID),
            'customer_email' => $quote->getCustomerEmail(),
            'date' => $creditmemoEntity->getCreatedAt(),
            'error_message' => 'Refund refused by Worldline'
        ];
    }
}
