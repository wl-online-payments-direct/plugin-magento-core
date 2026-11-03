<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\Order\Creditmemo;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Worldline\PaymentCore\Model\Config\Source\RefundReason as RefundReasonSource;
use Worldline\PaymentCore\Service\Refund\RefundReasonDataBuilder;

class RefundReason extends Template
{
    private $registry;

    private $refundReasonSource;

    private $refundReasonDataBuilder;

    public function __construct(
        Context $context,
        Registry $registry,
        RefundReasonSource $refundReasonSource,
        RefundReasonDataBuilder $refundReasonDataBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->refundReasonSource = $refundReasonSource;
        $this->refundReasonDataBuilder = $refundReasonDataBuilder;
    }

    public function isAvailable(): bool
    {
        $creditmemo = $this->registry->registry('current_creditmemo');
        if (!$creditmemo || !$creditmemo->getOrder()) {
            return false;
        }

        return $this->refundReasonDataBuilder->isApplicable((string)$creditmemo->getOrder()->getIncrementId());
    }

    public function getOptions(): array
    {
        return $this->refundReasonSource->toOptionArray();
    }

    public function getFieldName(): string
    {
        return 'creditmemo[' . RefundReasonDataBuilder::REFUND_REASON_FIELD . ']';
    }

    public function getFieldId(): string
    {
        return RefundReasonDataBuilder::REFUND_REASON_FIELD;
    }

    public function getSelectedReason(): string
    {
        $creditmemoData = (array)$this->getRequest()->getParam('creditmemo', []);
        $refundReason = (string)($creditmemoData[RefundReasonDataBuilder::REFUND_REASON_FIELD] ?? '');

        return $this->refundReasonSource->isValid($refundReason)
            ? $refundReason
            : RefundReasonSource::DEFAULT_REASON;
    }
}
