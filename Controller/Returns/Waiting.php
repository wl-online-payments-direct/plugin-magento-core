<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Returns;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;

class Waiting extends Action implements HttpGetActionInterface
{
    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    public function __construct(
        Context $context,
        QuoteResourceInterface $quoteResource
    ) {
        parent::__construct($context);
        $this->quoteResource = $quoteResource;
    }

    public function execute(): ResultInterface
    {
        $incrementId = $this->getRequest()->getParam('incrementId');
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('noRoute');
        if (!$incrementId) {
            return $redirect;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote || !$quote->getId()) {
            return $redirect;
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Waiting for payment confirmation'));

        return $resultPage;
    }
}
