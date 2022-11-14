<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\PaymentDetails;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionUpdater;

class Update extends Action implements HttpPostActionInterface
{
    /**
     * @var TransactionUpdater
     */
    private $transactionUpdater;

    public function __construct(
        Action\Context $context,
        TransactionUpdater $transactionUpdater
    ) {
        parent::__construct($context);
        $this->transactionUpdater = $transactionUpdater;
    }

    public function execute(): ResultInterface
    {
        $incrementId = (string) $this->getRequest()->getParam('increment_id');
        $storeId = (int) $this->getRequest()->getParam('store_id');

        $updateResult = $this->transactionUpdater->updateForIncrementId($incrementId, $storeId);
        if ($updateResult) {
            $this->messageManager->addSuccessMessage(__('Payment details have been updated.'));
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(['result' => $updateResult]);

        return $result;
    }
}
