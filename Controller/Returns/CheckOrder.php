<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Worldline\PaymentCore\Api\SessionDataManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
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

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        Context $context,
        SessionDataManagerInterface $sessionDataManager,
        OrderFactory $orderFactory,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->sessionDataManager = $sessionDataManager;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $incrementId = (string)$this->getRequest()->getParam('incrementId', '');
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $isOrderExist = (bool)$order->getId() && $this->belongsToCurrentSession($order, $incrementId);
        if ($isOrderExist) {
            $this->sessionDataManager->setOrderData($order);
        }

        $param['status'] = $isOrderExist;

        return $result->setData($param);
    }

    /**
     * Bind the lookup to the caller's own session so it cannot install a foreign order into the
     * attacker's session, nor act as an order-existence oracle for arbitrary increment ids.
     *
     * The legitimate waiting-page poll works because ReturnRequestProcessor::reserveOrder() stamps
     * LastRealOrderId on the buyer's return. getQuoteId() is intentionally NOT used: the webhook/cron
     * deactivates the quote once the order is created, which would break the real buyer's poll.
     */
    private function belongsToCurrentSession(OrderInterface $order, string $incrementId): bool
    {
        if ($incrementId === '' || (string)$this->checkoutSession->getLastRealOrderId() !== $incrementId) {
            return false;
        }

        if ($this->customerSession->isLoggedIn()
            && $order->getCustomerId()
            && (int)$order->getCustomerId() !== (int)$this->customerSession->getCustomerId()
        ) {
            return false;
        }

        return true;
    }
}
