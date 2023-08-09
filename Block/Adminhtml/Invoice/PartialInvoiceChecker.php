<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\Invoice;

use Magento\Backend\Block\Template;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;

class PartialInvoiceChecker extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentRepositoryInterface
     */
    private $wlPaymentRepository;

    /**
     * @var bool
     */
    private $isTemplateShowed = false;

    public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $wlPaymentRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->wlPaymentRepository = $wlPaymentRepository;
    }

    /**
     * @return string
     */
    public function toHtml(): string
    {
        if (!$this->isTemplateShowed && $this->isPartialInvoice()) {
            $this->isTemplateShowed = true;
            return parent::toHtml();
        }

        return '';
    }

    public function isPartialInvoice(): bool
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        $payment = $this->wlPaymentRepository->get($order->getIncrementId());
        $payProductLabel = PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[
        $payment->getPaymentProductId()
        ]['label'];

        $twintLabel = PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[
        PaymentProductsDetailsInterface::TWINT_PRODUCT_ID
        ]['label'];

        if ($payProductLabel == $twintLabel && (int)$order->getTotalQtyOrdered() > array_sum($invoiceItems)) {
            return true;
        }

        return false;
    }
}
