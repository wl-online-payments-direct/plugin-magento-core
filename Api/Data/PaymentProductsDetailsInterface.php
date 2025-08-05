<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Data;

/**
 * Worldline payment information
 *
 * @link https://support.direct.ingenico.com/en/payment-methods/view-by-payment-product/
 */
interface PaymentProductsDetailsInterface
{
    public const VISA_PRODUCT_ID = 1;
    public const AMERICAN_EXPRESS_PRODUCT_ID = 2;
    public const MASTER_CARD_PRODUCT_ID = 3;
    public const UNION_PAY_INT_ID = 56;
    public const MAESTRO_PRODUCT_ID = 117;
    public const JCB_PRODUCT_ID = 125;
    public const CARTE_BANCAIRE_PRODUCT_ID = 130;
    public const DINNERS_CLUB_PRODUCT_ID = 132;
    public const APPLE_PAY_PRODUCT_ID = 302;
    public const GOOGLE_PAY_PRODUCT_ID = 320;
    public const SEPA_DIRECT_DEBIT_PRODUCT_ID = 771;
    public const IDEAL_PRODUCT_ID = 809;
    public const PAYPAL_PRODUCT_ID = 840;
    public const BANCONTACT_PRODUCT_ID = 3012;
    public const GIFTCARD_LIMONETIK_ID = 3111;
    public const ILLICADO_PRODUCT_ID = 3112;
    public const PRZELEWY24_PRODUCT_ID = 3124;
    public const KLARNA_PAY_NOW_PRODUCT_ID = 3301;
    public const KLARNA_PAY_LATER_PRODUCT_ID = 3302;
    public const KLARNA_FINANCING_PRODUCT_ID = 3303;
    public const KLARNA_BANK_TRANSFER_PRODUCT_ID = 3304;
    public const KLARNA_DIRECT_TRANSFER_PRODUCT_ID = 3305;
    public const BIZUM_PRODUCT_ID = 5001;
    public const CPAY_PRODUCT_ID = 5100;
    public const ONEY_3X_4X_PRODUCT_ID = 5110;
    public const ONEY_FINANCEMENT_LONG_PRODUCT_ID = 5125;
    public const MEALVOUCHERS_PRODUCT_ID = 5402;
    public const CHEQUE_VACANCES_CONNECT_PRODUCT_ID = 5403;
    public const WECHAT_PAY_PRODUCT_ID = 5404;
    public const ALIPAY_PLUS_PRODUCT_ID = 5405;
    public const EPS_PRODUCT_ID = 5406;
    public const TWINT_PRODUCT_ID = 5407;
    public const BANK_TRANSFER_PRODUCT_ID = 5408;
    public const MULTIBANCO_PRODUCT_ID = 5500;
    public const ONEY_BRANDED_GIFT_CARD_PRODUCT_ID = 5600;
    public const INTERSOLVE_PRODUCT_ID = 5700;
    public const INTERSOLVE_AKTIESPORT_E_GIFTCARD_PRODUCT_ID = 5701;
    public const INTERSOLVE_AKTIESPORT_PRODUCT_ID = 5702;

    public const PAYMENT_GROUP_MOBILE = 'Mobile';
    public const PAYMENT_GROUP_CARD = 'Cards (debit & credit)';
    public const PAYMENT_GROUP_E_WALLET = 'e-Wallet';
    public const PAYMENT_GROUP_CONSUMER_CREDIT = 'Consumer Credit';
    public const PAYMENT_GROUP_REALTIME_BANKING = 'Real-time banking';
    public const PAYMENT_GROUP_GIFT_CARD = 'Gift card';
    public const PAYMENT_GROUP_INSTALMENT = 'Instalment';
    public const PAYMENT_GROUP_PREPAID = 'Prepaid';
    public const PAYMENT_GROUP_POSTPAID = 'Postpaid';
    public const PAYMENT_GROUP_DIRECT_DEBIT = 'Direct Debit';

    public const PAYMENT_PRODUCTS = [
        self::VISA_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Visa'
        ],
        self::AMERICAN_EXPRESS_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'American Express'
        ],
        self::MASTER_CARD_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Mastercard'
        ],
        self::MAESTRO_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Maestro'
        ],
        self::UNION_PAY_INT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Union Pay International'
        ],
        self::JCB_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'JCB'
        ],
        self::CARTE_BANCAIRE_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Carte Bancaire'
        ],
        self::DINNERS_CLUB_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Diners Club'
        ],
        self::APPLE_PAY_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_MOBILE,
            'label' => 'Apple Pay'
        ],
        self::GOOGLE_PAY_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_MOBILE,
            'label' => 'Google Pay'
        ],
        self::SEPA_DIRECT_DEBIT_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_DIRECT_DEBIT,
            'label' => 'SEPA Direct Debit'
        ],
        self::IDEAL_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_REALTIME_BANKING,
            'label' => 'iDEAL'
        ],
        self::PAYPAL_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_E_WALLET,
            'label' => 'PayPal'
        ],
        self::ALIPAY_PLUS_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_MOBILE,
            'label' => 'Alipay+'
        ],
        self::WECHAT_PAY_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_MOBILE,
            'label' => 'WeChat Pay'
        ],
        self::BANCONTACT_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CARD,
            'label' => 'Bancontact'
        ],
        self::GIFTCARD_LIMONETIK_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'Giftcard Limonetik'
        ],
        self::ILLICADO_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'Illicado'
        ],
        self::PRZELEWY24_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_REALTIME_BANKING,
            'label' => 'Przelewy24'
        ],
        self::KLARNA_PAY_NOW_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Klarna Pay Now'
        ],
        self::KLARNA_PAY_LATER_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Klarna Pay Later'
        ],
        self::KLARNA_FINANCING_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Klarna Financing'
        ],
        self::KLARNA_BANK_TRANSFER_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Klarna Bank Transfer'
        ],
        self::KLARNA_DIRECT_TRANSFER_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Klarna Direct Debit'
        ],
        self::BIZUM_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_E_WALLET,
            'label' => 'Bizum'
        ],
        self::CPAY_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_CONSUMER_CREDIT,
            'label' => 'Cpay'
        ],
        self::ONEY_3X_4X_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Oney 3x-4x'
        ],
        self::ONEY_FINANCEMENT_LONG_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_INSTALMENT,
            'label' => 'Oney Financement Long'
        ],
        self::MEALVOUCHERS_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_PREPAID,
            'label' => 'Mealvouchers'
        ],
        self::CHEQUE_VACANCES_CONNECT_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_PREPAID,
            'label' => 'Cheque Vacances Connect'
        ],
        self::MULTIBANCO_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_POSTPAID,
            'label' => 'Multibanco'
        ],
        self::EPS_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_REALTIME_BANKING,
            'label' => 'EPS'
        ],
        self::TWINT_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_REALTIME_BANKING,
            'label' => 'Twint'
        ],
        self::ONEY_BRANDED_GIFT_CARD_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'OneyBrandedGiftCard'
        ],
        self::INTERSOLVE_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'Intersolve'
        ],
        self::INTERSOLVE_AKTIESPORT_E_GIFTCARD_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'Intersolve'
        ],
        self::INTERSOLVE_AKTIESPORT_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_GIFT_CARD,
            'label' => 'Intersolve'
        ],
        self::BANK_TRANSFER_PRODUCT_ID => [
            'group' => self::PAYMENT_GROUP_REALTIME_BANKING,
            'label' => 'Bank Transfer by Worldline'
        ]
    ];
}
