<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Webhook;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class Refund
{
    public static function getData(string $incrementId = 'test01'): string
    {
        return <<<DATA
{
   "apiVersion":"v1",
   "created":"2023-01-23T09:51:27.9529667+01:00",
   "id":"0e3ce18a-3e5d-4c77-999e-f20ddb5ec6f9",
   "merchantId":"AmastyDirect",
   "refund":{
      "refundOutput":{
         "amountOfMoney":{
            "amount":2123,
            "currencyCode":"EUR"
         },
         "references":{
            "merchantReference":"$incrementId"
         },
         "cardPaymentMethodSpecificOutput":{
            "totalAmountPaid": 2123,
            "totalAmountRefunded": 0
         },
         "paymentMethod":"card"
      },
      "status":"REFUND_REQUESTED",
      "statusOutput":{
         "isCancellable":false,
         "statusCategory":"PENDING_CONNECT_OR_3RD_PARTY",
         "statusCode":8
      },
      "id":"3254564310_0"
   },
   "type":"refund.refund_requested"
}
DATA;
    }
}
