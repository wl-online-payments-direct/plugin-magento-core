<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Sales\Creditmemo\View;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;
use Worldline\PaymentCore\Model\Quote\Surcharging as QuoteSurcharging;

class Surcharging extends Template
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var SurchargingCreditmemoRepositoryInterface
     */
    private $surchargingCreditmemoRepository;

    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function initTotals(): Surcharging
    {
        $parent = $this->getParentBlock();
        $creditmemo = $parent->getCreditmemo();

        $surchargingCreditmemo = $this->surchargingCreditmemoRepository->getByCreditmemoId((int)$creditmemo->getId());
        if (!$surchargingCreditmemo->getId()) {
            return $this;
        }

        $total = [
            'code' => QuoteSurcharging::CODE,
            'strong' => false,
            'value' => $surchargingCreditmemo->getAmount(),
            'label' => __('Surcharging'),
        ];

        $surcharging = $this->dataObjectFactory->create()->setData($total);

        $parent->addTotal($surcharging, 'shipping');

        return $this;
    }
}
