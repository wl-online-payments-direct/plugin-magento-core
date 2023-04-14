<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\Plugin\Service\Refund;

use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\RefundResponseFactory;
use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;
use Worldline\PaymentCore\Infrastructure\StubData\Service\Refund\GetRefundResponse;
use Worldline\PaymentCore\Service\Refund\CreateRefundService;

class CreateRefundServiceMock
{
    /**
     * @var ServiceStubSwitcherInterface
     */
    private $serviceStubSwitcher;

    /**
     * @var RefundResponseFactory
     */
    private $refundResponseFactory;

    public function __construct(
        ServiceStubSwitcherInterface $serviceStubSwitcher,
        RefundResponseFactory $refundResponseFactory
    ) {
        $this->serviceStubSwitcher = $serviceStubSwitcher;
        $this->refundResponseFactory = $refundResponseFactory;
    }

    /**
     * @param CreateRefundService $subject
     * @param callable $proceed
     * @param string $paymentId
     * @param RefundRequest $refundRequest
     * @param int|null $storeId
     * @return RefundResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        CreateRefundService $subject,
        callable $proceed,
        string $paymentId,
        RefundRequest $refundRequest,
        ?int $storeId = null
    ): RefundResponse {
        if ($this->serviceStubSwitcher->isEnabled()) {
            $response = $this->refundResponseFactory->create();
            $response->fromJson(GetRefundResponse::getData($paymentId));

            return $response;
        }

        return $proceed($paymentId, $refundRequest, $storeId);
    }
}
