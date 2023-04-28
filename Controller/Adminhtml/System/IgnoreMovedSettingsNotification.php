<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\FlagManager;
use Worldline\PaymentCore\Model\ThreeDSecure\Notification\AdminNotification;

class IgnoreMovedSettingsNotification extends Action implements HttpGetActionInterface
{
    public const IGNORE_NOTIFICATION = 1;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(Context $context, FlagManager $flagManager)
    {
        parent::__construct($context);
        $this->flagManager = $flagManager;
    }

    public function execute(): Redirect
    {
        $this->flagManager->saveFlag(AdminNotification::FLAG_IDENTITY, self::IGNORE_NOTIFICATION);
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*');
    }
}
