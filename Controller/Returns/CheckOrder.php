<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\OrderFactory;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;

class CheckOrder extends Action implements HttpPostActionInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var SessionDataManagerInterface
     */
    private $sessionDataManager;

    public function __construct(
        Context $context,
        SessionDataManagerInterface $sessionDataManager,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->sessionDataManager = $sessionDataManager;
        $this->orderFactory = $orderFactory;
    }

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $incrementId = $this->getRequest()->getParam('incrementId', '');
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $isOrderExist = (bool)$order->getId();
        if ($isOrderExist) {
            $this->sessionDataManager->setOrderData($order);
        }

        $param['status'] = $isOrderExist;

        return $result->setData($param);
    }
}
