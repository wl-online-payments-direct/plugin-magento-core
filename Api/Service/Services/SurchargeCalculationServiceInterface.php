<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\Services;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;

interface SurchargeCalculationServiceInterface
{
    /**
     * @param CalculateSurchargeRequest $requestBody
     * @param int|null $storeId
     * @return array
     * @throws LocalizedException
     */
    public function execute(CalculateSurchargeRequest $requestBody, ?int $storeId = null): array;
}
