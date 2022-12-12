<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class Failed extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(Context $context, Session $checkoutSession, CartRepositoryInterface $cartRepository)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
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

        $this->clearQuote();

        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('checkout/cart');

        return $redirect;
    }

    private function clearQuote(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);

        $this->checkoutSession->clearQuote();
        $this->checkoutSession->clearStorage();
    }
}
