<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\Plugin\Service\Payment;

use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponseFactory;
use Worldline\PaymentCore\Api\Test\Infrastructure\ServiceStubSwitcherInterface;
use Worldline\PaymentCore\Service\Payment\GetPaymentDetailsService;
use Worldline\PaymentCore\Infrastructure\StubData\Service\Payment\GetPaymentDetailsServiceResponse;

class GetPaymentDetailsServiceMock
{
    /**
     * @var ServiceStubSwitcherInterface
     */
    private $serviceStubSwitcher;

    /**
     * @var PaymentDetailsResponseFactory
     */
    private $paymentDetailsResponseFactory;

    public function __construct(
        ServiceStubSwitcherInterface $serviceStubSwitcher,
        PaymentDetailsResponseFactory $paymentDetailsResponseFactory
    ) {
        $this->serviceStubSwitcher = $serviceStubSwitcher;
        $this->paymentDetailsResponseFactory = $paymentDetailsResponseFactory;
    }

    /**
     * @param GetPaymentDetailsService $subject
     * @param callable $proceed
     * @param string $paymentId
     * @param int|null $storeId
     * @return PaymentDetailsResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        GetPaymentDetailsService $subject,
        callable $proceed,
        string $paymentId,
        ?int $storeId = null
    ): PaymentDetailsResponse {
        if ($this->serviceStubSwitcher->isEnabled()) {
            $response = $this->paymentDetailsResponseFactory->create();
            $response->fromJson(GetPaymentDetailsServiceResponse::getData($paymentId));

            return $response;
        }

        return $proceed($paymentId, $storeId);
    }
}
