<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Ui;

use Magento\Framework\View\Asset\File;

interface PaymentIconsProviderInterface
{
    public function getIconById(?int $id, int $storeId): array;

    public function getIcons(int $storeId): array;

    public function createAsset(string $fileId, array $params = []): ?File;

    public function getDimensions(?File $asset = null): array;
}
