<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form;

class ReplaceSaveUrl
{
    private const WORLDLINE = 'worldline';

    public function afterGetSaveUrl(Form $subject, string $result): string
    {
        if (!$subject->getOrder()->getPayment()) {
            return $result;
        }

        $paymentMethodName = $subject->getOrder()->getPayment()->getMethod();
        if (strpos($paymentMethodName, self::WORLDLINE) !== 0) {
            return $result;
        }

        return $subject->getUrl('worldline/order_creditmemo/save', ['_current' => true]);
    }
}
