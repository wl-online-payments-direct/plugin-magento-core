<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\CanPlaceOrderContextManagerInterface;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterface;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterfaceFactory;

class CanPlaceOrderContextManager implements CanPlaceOrderContextManagerInterface
{
    /**
     * @var CanPlaceValidator
     */
    private $canPlaceValidator;

    /**
     * @var CanPlaceOrderContextInterfaceFactory
     */
    private $canPlaceOrderContextFactory;

    public function __construct(
        CanPlaceValidator $canPlaceValidator,
        CanPlaceOrderContextInterfaceFactory $canPlaceOrderContextFactory
    ) {
        $this->canPlaceValidator = $canPlaceValidator;
        $this->canPlaceOrderContextFactory = $canPlaceOrderContextFactory;
    }

    public function createContext(CartInterface $quote, int $statusCode): CanPlaceOrderContextInterface
    {
        $wlPaymentId = (string)$quote->getPayment()->getAdditionalInformation('payment_id');
        $context = $this->canPlaceOrderContextFactory->create();
        $context->setStatusCode($statusCode);
        $context->setWorldlinePaymentId($wlPaymentId);
        $context->setIncrementId($quote->getReservedOrderId());
        $context->setStoreId($quote->getStoreId());

        return $context;
    }

    public function canPlaceOrder(CanPlaceOrderContextInterface $context): bool
    {
        try {
            $this->canPlaceValidator->validate($context);
            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
