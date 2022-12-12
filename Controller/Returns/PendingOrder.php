<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;

class PendingOrder extends Action implements HttpPostActionInterface
{
    /**
     * @var PendingOrderManagerInterface
     */
    private $pendingOrderManager;

    public function __construct(
        Context $context,
        PendingOrderManagerInterface $pendingOrderManager
    ) {
        parent::__construct($context);
        $this->pendingOrderManager = $pendingOrderManager;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $incrementId = $this->getRequest()->getParam('incrementId', '');

        $param['status'] = $this->pendingOrderManager->processPendingOrder($incrementId);

        return $result->setData($param);
    }
}
