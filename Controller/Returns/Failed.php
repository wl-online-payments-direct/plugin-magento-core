<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

class Failed extends Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(Context $context, Session $checkoutSession)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(): Redirect
    {
        $this->messageManager->addSuccessMessage(
            __(
                'Thank you for your order %1.'
                . ' Your order is still being processed and you will receive a confirmation e-mail.'
                . ' Please contact us in case you don\'t receive the confirmation within 10 minutes.',
                $this->checkoutSession->getLastRealOrderId()
            )
        );

        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('checkout/cart');

        return $redirect;
    }
}
