<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook as WebhookResource;

class CleanWebhooks extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_PaymentCore::webhooks';

    /**
     * @var WebhookResource
     */
    private $webhookResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Context $context, WebhookResource $webhookResource, LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->webhookResource = $webhookResource;
        $this->logger = $logger;
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererUrl();

        try {
            $this->webhookResource->clearTable();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $resultRedirect;
    }
}
