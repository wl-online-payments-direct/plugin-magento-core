<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Refund;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundResponse;

interface CreateRefundServiceInterface
{
    /**
     * @param string $paymentId
     * @param RefundRequest $refundRequest
     * @param int|null $storeId
     * @return RefundResponse
     * @throws LocalizedException
     */
    public function execute(string $paymentId, RefundRequest $refundRequest, ?int $storeId = null): RefundResponse;
}
