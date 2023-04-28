<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Service\Payment;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class CancelPaymentServiceResponse
{
    public static function getData(string $paymentId, string $incrementId = 'test01'): string
    {
        $responsePool = [
            '3254564310' => static::getCancelPaymentResponse($incrementId),
            '3254564310_0' => static::getCancelPaymentResponse($incrementId)
        ];

        return $responsePool[$paymentId] ?? '';
    }

    public static function getCancelPaymentResponse(string $incrementId): string
    {
        return <<<DATA
{
    "payment": {
        "id": "3254564310_0",
        "paymentOutput": {
            "acquiredAmount": {
                "amount": 0,
                "currencyCode": "EUR"
            },
            "amountOfMoney": {
                "amount": 1500,
                "currencyCode": "EUR"
            },
            "cardPaymentMethodSpecificOutput": {
                "authorisationCode": "1878018784",
                "card": {
                    "bin": "424242",
                    "cardNumber": "************4242",
                    "countryCode": "99",
                    "expiryDate": "0523"
                },
                "fraudResults": {
                    "avsResult": "0",
                    "cvvResult": "0",
                    "fraudServiceResult": "accepted"
                },
                "paymentProductId": 1,
                "threeDSecureResults": {
                    "eci": "5",
                    "xid": "MzI2NTg1NTA1NQ=="
                }
            },
            "customer": {
                "device": {
                    "ipAddressCountryCode": "99"
                }
            },
            "paymentMethod": "card",
            "references": {
                "merchantReference": "$incrementId"
            }
        },
        "status": "CANCELLED",
        "statusOutput": {
            "isAuthorized": true,
            "isCancellable": false,
            "isRefundable": false,
            "statusCategory": "PENDING_MERCHANT",
            "statusCode": 61
        }
    }
}
DATA;
    }
}
