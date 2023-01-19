<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View;
use Magento\Sales\Model\Order\Creditmemo;

class UpdateStatusButton
{
    public function beforeSetLayout(View $subject): void
    {
        $creditmemo = $subject->getCreditmemo();
        if ((int)$creditmemo->getState() !== Creditmemo::STATE_OPEN) {
            return;
        }

        $payment = $creditmemo->getOrder()->getPayment();
        if (!$payment) {
            return;
        }

        $methodName = $payment->getMethod();
        if (strpos($methodName, 'worldline') === false) {
            return;
        }

        $subject->addButton(
            'creditmemo_update_status',
            [
                'label' => __('Update Status'),
                'class' => 'update-status',
                'onclick' => 'setLocation(\''
                    . $subject->getUrl(
                        'worldline/order_creditmemo/updateStatus',
                        [
                            'store_id' => $creditmemo->getStoreId(),
                            'creditmemo_id' => $creditmemo->getId(),
                            'grand_total' => $creditmemo->getGrandTotal(),
                            'increment_id' => $creditmemo->getOrder()->getIncrementId(),
                        ]
                    )
                    . '\')',
            ],
        );
    }
}
