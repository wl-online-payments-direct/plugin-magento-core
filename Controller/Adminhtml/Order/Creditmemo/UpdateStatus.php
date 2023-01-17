<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Worldline\PaymentCore\Model\RefundRequest\CreditmemoUpdater;

class UpdateStatus extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var CreditmemoUpdater
     */
    private $creditmemoUpdater;

    public function __construct(Context $context, CreditmemoUpdater $creditmemoUpdater)
    {
        parent::__construct($context);
        $this->creditmemoUpdater = $creditmemoUpdater;
    }

    /**
     * Update credit memo when webhooks are missing
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeId = (int) $this->getRequest()->getParam('store_id');
        $grandTotal = (string) $this->getRequest()->getParam('grand_total');
        $incrementId = (string) $this->getRequest()->getParam('increment_id');
        $creditmemoId = (string) $this->getRequest()->getParam('creditmemo_id');

        $this->creditmemoUpdater->update($incrementId, $grandTotal, $storeId);

        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);

        return $resultRedirect;
    }
}
