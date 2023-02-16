<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\ViewModel\Adminhtml\Order\View\Info;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Worldline\PaymentCore\Api\FraudRepositoryInterface;

/**
 * Provider for fraud information entity
 */
class FraudDataProvider implements ArgumentInterface
{
    /**
     * @var AbstractOrder
     */
    private $abstractOrder;

    /**
     * @var FraudRepositoryInterface
     */
    private $fraudRepository;

    public function __construct(AbstractOrder $abstractOrder, FraudRepositoryInterface $fraudRepository)
    {
        $this->abstractOrder = $abstractOrder;
        $this->fraudRepository = $fraudRepository;
    }

    public function getFraudInformation(): array
    {
        $fraud = $this->fraudRepository->getByIncrementId($this->abstractOrder->getOrder()->getIncrementId());

        if (!$fraud->getId()) {
            return [];
        }

        return [
            [
                'label' => __('Result'),
                'value' => ucfirst((string) $fraud->getResult()),
            ],
            [
                'label' => __('Liability'),
                'value' => ucfirst((string) $fraud->getLiability()),
            ],
            [
                'label' => __('Exemption'),
                'value' => ucfirst((string) $fraud->getExemption()),
            ],
            [
                'label' => __('Authentication status'),
                'value' => ucfirst((string) $fraud->getAuthenticationStatus()),
            ],
        ];
    }
}
