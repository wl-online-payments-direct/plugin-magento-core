<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\GraphQl\Model;

use Worldline\PaymentCore\GraphQl\Model\PaymentIcons\IconsPool;

class IconsRetriever
{
    /**
     * @var IconsPool
     */
    private $iconsPool;

    public function __construct(IconsPool $iconsPool)
    {
        $this->iconsPool = $iconsPool;
    }

    /**
     * @param string $code
     * @param string $originalCode
     * @param int $storeId
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIcons(string $code, string $originalCode, int $storeId): ?array
    {
        $retriever = $this->iconsPool->getIconsRetriever($code);
        if (!$retriever) {
            return null;
        }

        return $retriever->getIcons($code, $originalCode, $storeId);
    }
}
