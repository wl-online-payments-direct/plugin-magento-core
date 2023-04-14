<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\Plugin\Service\Payment;

use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use OnlinePayments\Sdk\Domain\CancelPaymentResponseFactory;
use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;
use Worldline\PaymentCore\Service\Payment\CancelPaymentService;
use Worldline\PaymentCore\Infrastructure\StubData\Service\Payment\CancelPaymentServiceResponse;

class CancelPaymentServiceMock
{
    /**
     * @var ServiceStubSwitcherInterface
     */
    private $serviceStubSwitcher;

    /**
     * @var CancelPaymentResponseFactory
     */
    private $cancelPaymentResponseFactory;

    public function __construct(
        ServiceStubSwitcherInterface $serviceStubSwitcher,
        CancelPaymentResponseFactory $cancelPaymentResponseFactory
    ) {
        $this->serviceStubSwitcher = $serviceStubSwitcher;
        $this->cancelPaymentResponseFactory = $cancelPaymentResponseFactory;
    }

    /**
     * @param CancelPaymentService $subject
     * @param callable $proceed
     * @param string $paymentId
     * @param int|null $storeId
     * @return CancelPaymentResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        CancelPaymentService $subject,
        callable $proceed,
        string $paymentId,
        ?int $storeId = null
    ): CancelPaymentResponse {
        if ($this->serviceStubSwitcher->isEnabled()) {
            $response = $this->cancelPaymentResponseFactory->create();
            $response->fromJson(CancelPaymentServiceResponse::getData($paymentId));

            return $response;
        }

        return $proceed($paymentId, $storeId);
    }
}
