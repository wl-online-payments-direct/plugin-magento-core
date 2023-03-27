<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;

interface CalculateSurchargeRequestBuilderInterface
{
    public function build(CartInterface $quote, string $hostedTokenizationId): CalculateSurchargeRequest;
}
