<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Ui;

interface PaymentProductsProviderInterface
{
    public function getPaymentProducts(int $storeId): array;

    public function savePaymentProductsToCache(array $paymentProducts, int $storeId): void;

    public function getPaymentProductsFromCache(int $storeId): array;

    public function generateCacheIdentifier(int $storeId): string;
}
