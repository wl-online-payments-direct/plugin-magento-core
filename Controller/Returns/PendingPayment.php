<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class PendingPayment extends Action implements HttpGetActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    public function execute(): ResultInterface
    {
        $this->clearQuote();

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Your payment is being processed'));
        return $resultPage;
    }

    private function clearQuote(): void
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $quote->setIsActive(false);
            $this->cartRepository->save($quote);

            $this->checkoutSession->clearQuote();
            $this->checkoutSession->clearStorage();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
