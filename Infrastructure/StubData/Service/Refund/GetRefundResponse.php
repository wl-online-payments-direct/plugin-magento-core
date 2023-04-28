<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Service\Refund;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class GetRefundResponse
{
    public static function getData(string $paymentId, string $incrementId = 'test01'): string
    {
        $responsePool = [
            '3254564310' => static::getRefundResponse($incrementId),
            '3254564310_0' => static::getRefundResponse($incrementId),
        ];

        return $responsePool[$paymentId] ?? '';
    }

    public static function getRefundResponse(string $incrementId): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":1500,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":1500,
         "currencyCode":"EUR"
      },
      "customer":{
         "device":{
            "ipAddressCountryCode":"99"
         }
      },
      "cardPaymentMethodSpecificOutput":{
         "paymentProductId":1,
         "authorisationCode":"537636403",
         "card":{
            "cardNumber":"************4675",
            "expiryDate":"0125",
            "bin":"433026",
            "countryCode":"BE"
         },
         "fraudResults":{
            "fraudServiceResult":"accepted",
            "avsResult":"0",
            "cvvResult":"0"
         },
         "threeDSecureResults":{
            "version":"2.2.0",
            "flow":"frictionless",
            "cavv":"AAABBEg0VhI0VniQEjRWAAAAAAA=",
            "eci":"5",
            "schemeEci":"05",
            "authenticationStatus":"Y",
            "acsTransactionId":"4C644F6D-F665-4DA3-B8C2-ECC7FFAACFA8",
            "dsTransactionId":"f25084f0-5b16-4c0a-ae5d-b24808a95e4b",
            "xid":"MzI1NDg4MjU1MQ==",
            "challengeIndicator":"no-challenge-requested",
            "liability":"issuer",
            "exemptionEngineFlow":"low-value-not-applicable-sca-requested-challenge-indicator-no-challenge-requested"
         },
         "token":"529f3c67-1613-4b1f-bf3e-b79cea0df81a"
      },
      "paymentMethod":"card"
   },
   "status":"PENDING_CAPTURE",
   "statusOutput":{
      "isCancellable":true,
      "statusCategory":"PENDING_MERCHANT",
      "statusCode":5,
      "isAuthorized":true,
      "isRefundable":false
   },
   "id":"3254564310_0"
}
DATA;
    }
}
