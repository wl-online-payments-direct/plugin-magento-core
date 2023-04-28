<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Service\Payment;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class GetPaymentDetailsServiceResponse
{
    public static function getData(string $paymentId, string $incrementId = 'test01'): string
    {
        $responsePool = [
            '3254564310_0' => static::getCreditCardResponse($incrementId),
            '3254564311_0' => static::getCreditCardResponseWithDiscount($incrementId),
            '3254564312_0' => static::getCreditCardResponseWithBundle($incrementId),
            '3254564313_0' => static::getCreditCardResponseWithConfigurable($incrementId),
            '3254564314_0' => static::getCreditCardResponseWithVirtual($incrementId),
            '3254564315_0' => static::getErrorCreditCardResponse($incrementId),
            '3254564316_0' => static::getCreditCardResponseWithSurcharging($incrementId)
        ];

        return $responsePool[$paymentId] ?? '{}';
    }

    public static function getCreditCardResponse(string $incrementId = 'test01'): string
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
            "amount":1500,
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

    public static function getCreditCardResponseWithDiscount(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":500,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":500,
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
         "id":"3254564311_0",
         "amountOfMoney":{
            "amount":500,
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
   "id":"3254564311_0"
}
DATA;
    }

    public static function getCreditCardResponseWithBundle(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":3500,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":3500,
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
         "id":"3254564312_0",
         "amountOfMoney":{
            "amount":3500,
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
   "id":"3254564312_0"
}
DATA;
    }

    public static function getCreditCardResponseWithConfigurable(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":5000,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":5000,
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
         "id":"3254564313_0",
         "amountOfMoney":{
            "amount":5000,
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
   "id":"3254564313_0"
}
DATA;
    }

    public static function getCreditCardResponseWithVirtual(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "paymentOutput":{
      "amountOfMoney":{
         "amount":1000,
         "currencyCode":"EUR"
      },
      "references":{
         "merchantReference":"$incrementId"
      },
      "acquiredAmount":{
         "amount":1000,
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
         "id":"3254564314_0",
         "amountOfMoney":{
            "amount":1000,
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
   "id":"3254564314_0"
}
DATA;
    }

    /**
     * @param string $incrementId
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getErrorCreditCardResponse(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
    "Operations": [
        {
            "amountOfMoney": {
                "amount": 6400,
                "currencyCode": "EUR"
            },
            "id": "3266382517_0",
            "paymentMethod": "card",
            "references": {
                "merchantReference": "$incrementId"
            },
            "status": "REJECTED",
            "statusOutput": {
                "errors": [
                    {
                        "category": "PAYMENT_PLATFORM_ERROR",
                        "code": "9999",
                        "errorCode": "40001134",
                        "httpStatusCode": 403,
                        "id": "AUTHENTICATION_FAILURE",
                        "message": "",
                        "retriable": false
                    },
                    {
                        "category": "PAYMENT_PLATFORM_ERROR",
                        "errorCode": "50001111",
                        "id": "Error coming from a third party",
                        "message": "cardholder"
                    }
                ],
                "isAuthorized": false,
                "isCancellable": false,
                "isRefundable": false,
                "statusCategory": "UNSUCCESSFUL",
                "statusCode": 2
            }
        }
    ],
    "id": "3254564315_0",
    "paymentOutput": {
        "acquiredAmount": {
            "amount": 0,
            "currencyCode": "EUR"
        },
        "amountOfMoney": {
            "amount": 1000,
            "currencyCode": "EUR"
        },
        "cardPaymentMethodSpecificOutput": {
            "card": {
                "bin": "445002",
                "cardNumber": "************3103",
                "countryCode": "IN",
                "expiryDate": "0124"
            },
            "fraudResults": {
                "cvvResult": "P",
                "fraudServiceResult": "accepted"
            },
            "paymentProductId": 1,
            "threeDSecureResults": {
                "acsTransactionId": "8A74A01B-659D-496D-AFAB-827A518332E3",
                "authenticationStatus": "R",
                "eci": "91",
                "exemptionEngineFlow":
                "low-value-not-applicable-sca-requested-challenge-indicator-no-challenge-requested",
                "flow": "challenge",
                "version": "2.2.0",
                "xid": "MzI2NjM4MjUxNw=="
            },
            "token": "1dca9c1f-3210-4e7d-9a25-6cc84ff7216f"
        },
        "customer": {
            "device": {
                "ipAddressCountryCode": "99"
            }
        },
        "paymentMethod": "card",
        "references": {
            "merchantReference": "000000578"
        }
    },
    "status": "REJECTED",
    "statusOutput": {
        "errors": [
            {
                "category": "PAYMENT_PLATFORM_ERROR",
                "code": "9999",
                "errorCode": "40001134",
                "httpStatusCode": 403,
                "id": "AUTHENTICATION_FAILURE",
                "message": "",
                "retriable": false
            },
            {
                "category": "PAYMENT_PLATFORM_ERROR",
                "errorCode": "50001111",
                "id": "Error coming from a third party",
                "message": "cardholder"
            }
        ],
        "isAuthorized": false,
        "isCancellable": false,
        "isRefundable": false,
        "statusCategory": "UNSUCCESSFUL",
        "statusCode": 2
    }
}
DATA;
    }

    public static function getCreditCardResponseWithSurcharging(string $incrementId = 'test01'): string
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
      "surchargeSpecificOutput":{
         "surchargeAmount":{
         "amount":1000,
         "currencyCode":"EUR"
         }
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
            "amount":1500,
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
   "id":"3254564316_0"
}
DATA;
    }
}
