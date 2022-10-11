<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\OrderFactory;

class CheckOrder extends Action
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $incrementId = $this->getRequest()->getParam('incrementId', '');
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $isOrderExist = (bool)$order->getId();
        if ($isOrderExist) {
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($incrementId);
            $this->checkoutSession->setLastQuoteId($order->getQuoteId());
            $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        }

        $param['status'] = $isOrderExist;

        return $result->setData($param);
    }
}
