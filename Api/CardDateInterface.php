<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;

interface CardDateInterface
{
    public function getExpirationDateAt(CardPaymentMethodSpecificOutput $cardPaymentMethodSO): string;

    public function getExpirationDate(CardPaymentMethodSpecificOutput $cardPaymentMethodSO): string;

    public function processDate(string $date): \DateTime;

    public function convertDetailsToJSON(array $details): string;
}
