<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\StubData\Service\Payment;

use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;

/**
 * phpcs:disable Magento2.Functions.StaticFunction
 */
class GetPaymentProductsServiceResponse
{
    public static function getData(GetPaymentProductsParams $queryParams): string
    {
        $responsePool = [
            'US' => static::getResponse(),
        ];

        return $responsePool[$queryParams->getCountryCode()] ?? '';
    }

    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return string
     */
    public static function getResponse(): string
    {
        return <<<DATA
{
    "paymentProducts": [
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 0,
                "label": "alipay_plus",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/alipay_plus.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 0,
                    "label": "alipay_plus",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/alipay_plus.gif"
                }
            ],
            "fields": [],
            "id": 5405,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 1,
                "label": "American Express",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/American Express.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 1,
                    "label": "American Express",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/American Express.gif"
                }
            ],
            "fields": [],
            "id": 2,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 2,
                "label": "APPLEPAY",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/APPLEPAY.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 2,
                    "label": "APPLEPAY",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/APPLEPAY.gif"
                }
            ],
            "fields": [],
            "id": 302,
            "paymentMethod": "mobile",
            "paymentProduct302SpecificData": {
                "networks": [
                    "Visa",
                    "MasterCard"
                ]
            },
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 3,
                "label": "BCMC",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/BCMC.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 3,
                    "label": "BCMC",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/BCMC.gif"
                }
            ],
            "fields": [],
            "id": 3012,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 4,
                "label": "Bizum",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Bizum.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 4,
                    "label": "Bizum",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Bizum.gif"
                }
            ],
            "fields": [],
            "id": 5001,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 5,
                "label": "CB",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/CB.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 5,
                    "label": "CB",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/CB.gif"
                }
            ],
            "fields": [],
            "id": 130,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 6,
                "label": "Diners Club",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Diners Club.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 6,
                    "label": "Diners Club",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Diners Club.gif"
                }
            ],
            "fields": [],
            "id": 132,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 7,
                "label": "MasterCard",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Eurocard.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 7,
                    "label": "MasterCard",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Eurocard.gif"
                }
            ],
            "fields": [],
            "id": 3,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 8,
                "label": "GOOGLEPAY",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/GOOGLEPAY.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 8,
                    "label": "GOOGLEPAY",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/GOOGLEPAY.gif"
                }
            ],
            "fields": [],
            "id": 320,
            "paymentMethod": "mobile",
            "paymentProduct320SpecificData": {
                "gateway": "worldlineingenicoogone",
                "networks": [
                    "VISA",
                    "MASTERCARD"
                ]
            },
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 9,
                "label": "iDeal",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/iDeal.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 9,
                    "label": "iDeal",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/iDeal.gif"
                }
            ],
            "fields": [],
            "id": 809,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 10,
                "label": "ILLICADO",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/ILLICADO.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 10,
                    "label": "ILLICADO",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/ILLICADO.gif"
                }
            ],
            "fields": [],
            "id": 3112,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 11,
                "label": "Intersolve",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Intersolve.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 11,
                    "label": "Intersolve",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Intersolve.gif"
                }
            ],
            "fields": [],
            "id": 5700,
            "paymentMethod": "card",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 12,
                "label": "JCB",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/JCB.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 12,
                    "label": "JCB",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/JCB.gif"
                }
            ],
            "fields": [],
            "id": 125,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 13,
                "label": "KLARNA_BANK_TRANSFER",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_BANK_TRANSFER.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 13,
                    "label": "KLARNA_BANK_TRANSFER",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_BANK_TRANSFER.gif"
                }
            ],
            "fields": [],
            "id": 3304,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 14,
                "label": "KLARNA_PAYLATER",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_PAYLATER.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 14,
                    "label": "KLARNA_PAYLATER",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_PAYLATER.gif"
                }
            ],
            "fields": [],
            "id": 3302,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 15,
                "label": "KLARNA_PAYNOW",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_PAYNOW.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 15,
                    "label": "KLARNA_PAYNOW",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/KLARNA_PAYNOW.gif"
                }
            ],
            "fields": [],
            "id": 3301,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 16,
                "label": "Maestro",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Maestro.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 16,
                    "label": "Maestro",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Maestro.gif"
                }
            ],
            "fields": [],
            "id": 117,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 17,
                "label": "Mealvouchers",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Mealvouchers.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 17,
                    "label": "Mealvouchers",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Mealvouchers.gif"
                }
            ],
            "fields": [],
            "id": 5402,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 18,
                "label": "Multibanco",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Multibanco.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 18,
                    "label": "Multibanco",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Multibanco.gif"
                }
            ],
            "fields": [],
            "id": 5500,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 19,
                "label": "Oney3x4x",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Oney3x4x.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 19,
                    "label": "Oney3x4x",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Oney3x4x.gif"
                }
            ],
            "fields": [],
            "id": 5110,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 20,
                "label": "OneyBrandedGiftCard",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/OneyBrandedGiftCard.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 20,
                    "label": "OneyBrandedGiftCard",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/OneyBrandedGiftCard.gif"
                }
            ],
            "fields": [],
            "id": 5600,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 21,
                "label": "PAYPAL",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/PAYPAL.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 21,
                    "label": "PAYPAL",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/PAYPAL.gif"
                }
            ],
            "fields": [],
            "id": 840,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 22,
                "label": "SepaDirectDebit",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/SepaDirectDebit.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 22,
                    "label": "SepaDirectDebit",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/SepaDirectDebit.gif"
                }
            ],
            "fields": [],
            "id": 771,
            "paymentMethod": "directDebit",
            "usesRedirectionTo3rdParty": true
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 23,
                "label": "Sodexo Sport & Culture",
                "logo":
                "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Sodexo Sport & Culture.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 23,
                    "label": "Sodexo Sport & Culture",
                    "logo":
                    "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/Sodexo Sport & Culture.gif"
                }
            ],
            "fields": [],
            "id": 5772,
            "paymentMethod": "card",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": true,
            "allowsTokenization": true,
            "displayHints": {
                "displayOrder": 24,
                "label": "VISA",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/VISA.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 24,
                    "label": "VISA",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/VISA.gif"
                }
            ],
            "fields": [],
            "id": 1,
            "paymentMethod": "card",
            "paymentProductGroup": "Cards",
            "usesRedirectionTo3rdParty": false
        },
        {
            "allowsRecurring": false,
            "allowsTokenization": false,
            "displayHints": {
                "displayOrder": 25,
                "label": "wechat",
                "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/wechat.gif"
            },
            "displayHintsList": [
                {
                    "displayOrder": 25,
                    "label": "wechat",
                    "logo": "https:\/\/assets.test.cdn.v-psp.com\/s2s\/dda64ecf143c424d6523\/images\/pm\/wechat.gif"
                }
            ],
            "fields": [],
            "id": 5404,
            "paymentMethod": "redirect",
            "usesRedirectionTo3rdParty": true
        }
    ]
}
DATA;
    }
}
