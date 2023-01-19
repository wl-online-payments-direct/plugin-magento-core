<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
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

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $incrementId = $this->getRequest()->getParam('incrementId', '');
            $param['status'] = $this->pendingOrderManager->processPendingOrder($incrementId);
            return $result->setData($param);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $result->setData([
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $result->setData([
                'error' => __('Sorry, but something went wrong'),
            ]);
        }
    }
}
