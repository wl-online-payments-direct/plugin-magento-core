<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentResponse;

interface CreatePaymentServiceInterface
{
    /**
     * Create payment
     *
     * @param CreatePaymentRequest $request
     * @param int|null $storeId
     * @return CreatePaymentResponse
     * @throws LocalizedException
     */
    public function execute(CreatePaymentRequest $request, ?int $storeId = null): CreatePaymentResponse;
}
