<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Webhooks extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_PaymentCore::webhooks';

    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->initLayout();
        $resultPage->setActiveMenu('Worldline_PaymentCore::webhooks');
        $resultPage->getConfig()->getTitle()->prepend(__('Worldline Webhooks'));

        return $resultPage;
    }
}
