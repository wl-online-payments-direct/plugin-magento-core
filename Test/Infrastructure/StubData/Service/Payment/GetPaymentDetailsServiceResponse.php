<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Infrastructure\StubData\Service\Payment;

class GetPaymentDetailsServiceResponse
{
    public static function getData(string $paymentId, string $incrementId = 'test01'): string
    {
        $responsePool = [
            '3254564310_0' => static::getCreditCardResponse($incrementId),
        ];

        return $responsePool[$paymentId] ?? '{}';
    }

    public static function getCreditCardResponse(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":8100,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":8100,
         "currencyCode":"EUR"
      },
      "customer":{
         "device":{
            "ipAddressCountryCode":"99"
         }
      },
      "cardPaymentMethodSpecificOutput":{
         "paymentProductId":1,
         "authorisationCode":"52188481",
         "card":{
            "cardNumber":"************4675",
            "expiryDate":"0123",
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
            "xid":"MzI1NDc5OTg1MA==",
            "challengeIndicator":"no-challenge-requested",
            "liability":"issuer",
            "exemptionEngineFlow":"low-value-not-applicable-sca-requested-challenge-indicator-no-challenge-requested"
         },
         "token":"a856de2a-3ce8-4113-a873-7ae5218e18bc"
      },
      "paymentMethod":"card"
   },
   "Operations":[
      {
         "id":"3254564310_0",
         "amountOfMoney":{
            "amount":8100,
            "currencyCode":"EUR"
         },
         "status":"CAPTURED",
         "statusOutput":{
            "isCancellable":false,
            "statusCategory":"COMPLETED",
            "statusCode":9,
            "isAuthorized":false,
            "isRefundable":true
         },
         "paymentMethod":"card",
         "references":{
            "merchantReference":"000000007"
         }
      }
   ],
   "status":"CAPTURED",
   "statusOutput":{
      "isCancellable":false,
      "statusCategory":"COMPLETED",
      "statusCode":9,
      "isAuthorized":false,
      "isRefundable":true
   },
   "id":"3254564310_0"
}
DATA;
    }
}
