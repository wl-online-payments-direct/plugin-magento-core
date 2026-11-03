<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Refund;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\RefundRedirectPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\RefundRedirectPaymentProduct900SpecificInputFactory;
use OnlinePayments\Sdk\Domain\RefundRequest;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;
use Worldline\PaymentCore\Model\Config\Source\RefundReason;

class RefundReasonDataBuilder
{
    public const REFUND_REASON_FIELD = 'worldline_refund_reason';

    private $paymentRepository;

    private $paymentIdFormatter;

    private $request;

    private $refundReasonSource;

    private $refundRedirectPaymentMethodSpecificInputFactory;

    private $refundRedirectPaymentProduct900SpecificInputFactory;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        PaymentIdFormatterInterface $paymentIdFormatter,
        RequestInterface $request,
        RefundReason $refundReasonSource,
        RefundRedirectPaymentMethodSpecificInputFactory $refundRedirectPaymentMethodSpecificInputFactory,
        RefundRedirectPaymentProduct900SpecificInputFactory $refundRedirectPaymentProduct900SpecificInputFactory
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->paymentIdFormatter = $paymentIdFormatter;
        $this->request = $request;
        $this->refundReasonSource = $refundReasonSource;
        $this->refundRedirectPaymentMethodSpecificInputFactory = $refundRedirectPaymentMethodSpecificInputFactory;
        $this->refundRedirectPaymentProduct900SpecificInputFactory =
            $refundRedirectPaymentProduct900SpecificInputFactory;
    }

    public function isApplicable(string $incrementId): bool
    {
        $payment = $this->paymentRepository->get($incrementId);

        return (int)$payment->getPaymentProductId() === PaymentProductsDetailsInterface::WERO_PRODUCT_ID;
    }

    public function apply(RefundRequest $refundRequest, string $incrementId, ?string $paymentId = null): void
    {
        $payment = $this->paymentRepository->get($incrementId);
        if ((int)$payment->getPaymentProductId() !== PaymentProductsDetailsInterface::WERO_PRODUCT_ID) {
            return;
        }

        if ($paymentId !== null && !$this->isSamePayment((string)$payment->getPaymentId(), $paymentId)) {
            return;
        }

        $paymentProduct900SpecificInput = $this->refundRedirectPaymentProduct900SpecificInputFactory->create();
        $paymentProduct900SpecificInput->setRefundReason($this->getRefundReason());

        $redirectPaymentMethodSpecificInput = $this->refundRedirectPaymentMethodSpecificInputFactory->create();
        $redirectPaymentMethodSpecificInput->setRefundRedirectPaymentProduct900SpecificInput(
            $paymentProduct900SpecificInput
        );

        $refundRequest->setRefundRedirectPaymentMethodSpecificInput($redirectPaymentMethodSpecificInput);
    }

    private function getRefundReason(): string
    {
        $creditmemoData = (array)$this->request->getParam('creditmemo', []);
        $refundReason = (string)($creditmemoData[self::REFUND_REASON_FIELD] ?? '');

        return $this->refundReasonSource->isValid($refundReason) ? $refundReason : RefundReason::DEFAULT_REASON;
    }

    private function isSamePayment(string $storedPaymentId, string $paymentId): bool
    {
        try {
            return $this->paymentIdFormatter->validateAndFormat($storedPaymentId)
                === $this->paymentIdFormatter->validateAndFormat($paymentId);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
