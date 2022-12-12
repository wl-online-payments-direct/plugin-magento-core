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
        1    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Visa'],
        2    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'American Express'],
        3    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Mastercard'],
        117  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Maestro'],
        125  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'JCB'],
        130  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Carte Bancaire'],
        132  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Diners Club'],
        302  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Apple Pay'],
        320  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Google Pay'],
        771  => ['group' => self::PAYMENT_GROUP_DIRECT_DEBIT,     'label' => 'SEPA Direct Debit'],
        809  => ['group' => self::PAYMENT_GROUP_REALTIME_BANKING, 'label' => 'iDEAL'],
        840  => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Paypal'],
        861  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Alipay'],
        863  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'WeChat Pay'],
        3012 => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Bancontact'],
        3112 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Illicado'],
        3301 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Pay Now'],
        3302 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Pay Later'],
        3303 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Financing'],
        3304 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Bank Transfer'],
        3305 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna Direct Debit'],
        5001 => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Bizum'],
        5100 => ['group' => self::PAYMENT_GROUP_CONSUMER_CREDIT,  'label' => 'Cpay'],
        5110 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney 3x-4x'],
        5125 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney Financement Long'],
        5402 => ['group' => self::PAYMENT_GROUP_PREPAID,          'label' => 'Mealvouchers'],
        5500 => ['group' => self::PAYMENT_GROUP_POSTPAID,         'label' => 'Multibanco'],
        5600 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'OneyBrandedGiftCard'],
        5700 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Intersolve']
    ];
}
