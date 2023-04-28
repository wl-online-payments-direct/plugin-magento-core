<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Service\Services;

use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class SurchargeCalculationResponse
{
    public static function getData(CalculateSurchargeRequest $requestBody): string
    {
        $netAmount = $requestBody->getAmountOfMoney()->getAmount();
        $responsePool = [
            $netAmount => static::getSurchargeResponse($netAmount),
        ];

        return $responsePool[$netAmount] ?? '';
    }

    public static function getSurchargeResponse(int $netAmount): string
    {
        return <<<DATA
{
  "surcharges": [
    {
      "paymentProductId": 129,
      "result": "OK",
      "netAmount": {
        "amount": $netAmount,
        "currencyCode": "EUR"
      },
      "surchargeAmount": {
        "amount": 1000,
        "currencyCode": "EUR"
      },
      "totalAmount": {
        "amount": 1000,
        "currencyCode": "EUR"
      },
      "surchargeRate": {
        "surchargeProductTypeId": "VISA_DOMESTIC_DEBIT",
        "surchargeProductTypeVersion": "8667F70E-9DDB-41FF-8822-A00FC43FCCAF",
        "adValoremRate": 2.5,
        "specificRate": 20
      }
    }
  ]
}
DATA;
    }
}
