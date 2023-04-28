<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Worldline\PaymentCore\Api\PendingOrderManagerInterface;
use Worldline\PaymentCore\Model\Order\FailedOrderCreationNotification;

class PendingOrder extends Action implements HttpPostActionInterface
{
    /**
     * @var PendingOrderManagerInterface
     */
    private $pendingOrderManager;

    /**
     * @var FailedOrderCreationNotification
     */
    private $failedOrderCreationNotification;

    public function __construct(
        Context $context,
        PendingOrderManagerInterface $pendingOrderManager,
        FailedOrderCreationNotification $failedOrderCreationNotification
    ) {
        parent::__construct($context);
        $this->pendingOrderManager = $pendingOrderManager;
        $this->failedOrderCreationNotification = $failedOrderCreationNotification;
    }

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $incrementId = $this->getRequest()->getParam('incrementId', '');

        try {
            $param['status'] = $this->pendingOrderManager->processPendingOrder($incrementId);
            return $result->setData($param);
        } catch (LocalizedException $e) {
            return $result->setData([
                'error' => $this->processException($incrementId, $e->getMessage()),
            ]);
        } catch (Exception $e) {
            return $result->setData([
                'error' => $this->processException($incrementId, __('Sorry, but something went wrong')),
            ]);
        }
    }

    /**
     * @param string $incrementId
     * @param $errorMessage
     * @return string|Phrase
     * @throws MailException
     */
    private function processException(string $incrementId, $errorMessage)
    {
        $this->failedOrderCreationNotification->notify(
            $incrementId,
            (string)$errorMessage,
            FailedOrderCreationNotification::WAITING_PAGE_SPACE
        );
        $this->messageManager->addErrorMessage($errorMessage);
        return $errorMessage;
    }
}
