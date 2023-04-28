<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class EmailNotification
{
    /**
     * @var SalesData
     */
    private $salesData;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    public function __construct(
        SalesData $salesData,
        CreditmemoSender $creditmemoSender
    ) {
        $this->salesData = $salesData;
        $this->creditmemoSender = $creditmemoSender;
    }

    public function send(CreditmemoInterface $creditmemo): void
    {
        if ($this->salesData->canSendNewCreditmemoEmail() && $creditmemo->getOrder()->getCustomerNoteNotify()) {
            $this->creditmemoSender->send($creditmemo);
        }
    }
}
