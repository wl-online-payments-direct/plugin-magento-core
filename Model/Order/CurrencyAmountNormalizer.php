<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

class CurrencyAmountNormalizer
{
    private static $map = [
        'BHD' => 3,
        'XOF' => 0,
        'BIF' => 0,
        'XAF' => 0,
        'CLP' => 0,
        'CLF' => 4,
        'KMF' => 0,
        'DJF' => 0,
        'XPF' => 0,
        'GNF' => 0,
        'ISK' => 0,
        'IQD' => 3,
        'JPY' => 0,
        'JOD' => 3,
        'KRW' => 0,
        'KWD' => 3,
        'LYD' => 3,
        'OMR' => 3,
        'PYG' => 0,
        'RWF' => 0,
        'TND' => 3,
        'UGX' => 0,
        'UYI' => 0,
        'UYW' => 4,
        'VUV' => 0,
        'VND' => 0,
    ];

    /**
     * Normalize WL amount into Magento's float representation based on currency
     *
     * @param float $amount
     * @param string $currency
     * @param bool $multiply
     *
     * @return float
     */
    public function normalize(float $amount, string $currency, $multiply = false): float
    {
        $decimals = self::$map[$currency] ?? 2;

        if ($decimals === 0) {
            return (float)$amount;
        }

        return $multiply ?  (float)$amount * (10 ** $decimals) : (float)$amount / (10 ** $decimals);
    }
}
