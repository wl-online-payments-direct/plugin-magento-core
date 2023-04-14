<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Webhook;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class AuthorizationWithConfigurable
{
    public static function getData(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "apiVersion":"v1",
   "created":"2023-01-23T09:51:27.9529667+01:00",
   "id":"0e3ce18a-3e5d-4c77-999e-f20ddb5ec6f9",
   "merchantId":"AmastyDirect",
   "payment":{
      "paymentOutput":{
         "amountOfMoney":{
            "amount":5000,
            "currencyCode":"EUR"
         },
         "references":{
            "merchantReference":"$incrementId"
         },
         "customer":{
            "device":{
               "ipAddressCountryCode":"DE"
            }
         },
         "cardPaymentMethodSpecificOutput":{
            "paymentProductId":2,
            "authorisationCode":"test123",
            "card":{
               "cardNumber":"***********7346",
               "expiryDate":"1226",
               "bin":"375418",
               "countryCode":"99"
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
               "acsTransactionId":"1840F3EA-58FF-458E-A29E-677E13227CBA",
               "dsTransactionId":"02020000f25084f05b164c0aae5db24808a95e4b",
               "liability":"issuer"
            }
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
      "id":"3254564313_0"
   },
   "type":"payment.pending_capture"
}
DATA;
    }
}
