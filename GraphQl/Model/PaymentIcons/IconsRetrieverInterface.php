<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\GraphQl\Model\PaymentIcons;

interface IconsRetrieverInterface
{
    public const ICON_TITLE = 'icon_title';
    public const ICON_URL = 'icon_url';

    public function getIcons(string $code, string $originalCode, int $storeId): array;
}
