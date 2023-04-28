<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Sales\Creditmemo\Create;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Worldline\PaymentCore\Api\Data\SurchargingCreditmemoInterface;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Model\Quote\Surcharging as QuoteSurcharging;

class Surcharging extends Template
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    /**
     * @var SurchargingCreditmemoRepositoryInterface
     */
    private $surchargingCreditmemoRepository;

    /**
     * @var DataObject
     */
    private $surchargingQuote;

    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function initTotals(): Surcharging
    {
        $parent = $this->getParentBlock();
        if (!$parent->getOrder()) {
            return $this;
        }

        $quoteId = (int)$parent->getOrder()->getQuoteId();
        $this->surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        if (!$this->surchargingQuote->getId()
            || $parent->getOrder()->getPayment()->getMethod() !== $this->surchargingQuote->getPaymentMethod()
        ) {
            return $this;
        }

        $total = ['code' => QuoteSurcharging::CODE, 'block_name' => $this->getNameInLayout()];
        $surcharging = $this->dataObjectFactory->create()->setData($total);
        $parent->addTotal($surcharging, 'shipping');

        return $this;
    }

    public function formatValue(float $value): string
    {
        /** @var Order $order */
        $order = $this->getParentBlock()->getOrder();

        return $order->getOrderCurrency()->formatPrecision(
            $value,
            2,
            ['display' => 1],
            false
        );
    }

    public function hasSurcharging(): bool
    {
        if ($this->surchargingQuote->getId()) {
            return true;
        }

        return false;
    }

    public function getSurchargeAmount(): float
    {
        $surchargingAmount = 0.0;
        if ($this->surchargingQuote->getIsRefunded() || !$this->getParentBlock()->getOrder()) {
            return $surchargingAmount;
        }

        $enteredCreditmemoData = $this->getRequest()->getParam('creditmemo');
        if ($enteredCreditmemoData && !empty($enteredCreditmemoData[QuoteSurcharging::CODE])) {
            return (float)$enteredCreditmemoData[QuoteSurcharging::CODE];
        }

        $quoteId = (int)$this->getParentBlock()->getOrder()->getQuoteId();
        $surchargingAmount = $this->surchargingQuote->getAmount();
        /** @var SurchargingCreditmemoInterface $surchargingCreditmemo */
        foreach ($this->surchargingCreditmemoRepository->getItemsByQuoteId($quoteId) as $surchargingCreditmemo) {
            $surchargingAmount -= $surchargingCreditmemo->getAmount();
        }

        return (float)$surchargingAmount;
    }
}
