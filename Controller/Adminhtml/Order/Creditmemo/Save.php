<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\Order\Creditmemo;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Model\RefundRequest\CreditmemoOnlineService;
use Worldline\PaymentCore\Model\RefundRequest\EmailNotification;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var CreditmemoLoader
     */
    private $creditmemoLoader;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var CreditmemoOnlineService
     */
    private $refundOnlineService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditmemoManagement;

    /**
     * @var EmailNotification
     */
    private $emailNotification;

    public function __construct(
        Action\Context $context,
        CreditmemoLoader $creditmemoLoader,
        ForwardFactory $resultForwardFactory,
        CreditmemoOnlineService $refundOnlineService,
        LoggerInterface $logger,
        CreditmemoManagementInterface $creditmemoManagement,
        EmailNotification $emailNotification
    ) {
        parent::__construct($context);

        $this->creditmemoLoader = $creditmemoLoader;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->refundOnlineService = $refundOnlineService;
        $this->logger = $logger;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->emailNotification = $emailNotification;
    }

    /**
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParam('creditmemo', []);

        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            $creditmemo = $this->loadCreditMemo();
            if (!$creditmemo) {
                return $this->resultForwardFactory->create()->forward('noroute');
            }

            if (!$creditmemo->isValidGrandTotal()) {
                throw new LocalizedException(__('The credit memo\'s total must be positive.'));
            }

            $this->addComment($creditmemo, $data);
            $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

            if ($this->isOffline()) {
                $this->creditmemoManagement->refund($creditmemo, true);
                $this->emailNotification->send($creditmemo);
                $this->messageManager->addSuccessMessage(__('You created the credit memo.'));
            } else {
                $this->refundOnlineService->refund($creditmemo);
                $this->messageManager->addSuccessMessage(__('The credit memo request has been sent.'));
            }

            $this->_getSession()->getCommentText(true);
            $resultRedirect->setPath('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);

            return $resultRedirect;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('We can\'t save the credit memo right now.'));
        }

        $resultRedirect->setPath('sales/order_creditmemo/new', ['_current' => true]);
        return $resultRedirect;
    }

    /**
     * @return false|Creditmemo
     */
    private function loadCreditMemo()
    {
        $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
        $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));

        return $this->creditmemoLoader->load();
    }

    /**
     * @param Creditmemo $creditmemo
     * @param array $data
     * @return void
     */
    private function addComment(Creditmemo $creditmemo, array $data): void
    {
        if (!empty($data['comment_text'])) {
            $creditmemo->addComment(
                $data['comment_text'],
                isset($data['comment_customer_notify']),
                isset($data['is_visible_on_front'])
            );

            $creditmemo->setCustomerNote($data['comment_text']);
            $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
        }
    }

    private function isOffline(): bool
    {
        $data = $this->getRequest()->getParam('creditmemo', []);
        return isset($data['do_offline']) && (bool)$data['do_offline'];
    }
}
