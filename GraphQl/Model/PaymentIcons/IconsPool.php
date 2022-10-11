<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\GraphQl\Model\PaymentIcons;

class IconsPool
{
    /**
     * @var IconsRetrieverInterface[]
     */
    private $iconsRetrievers;

    public function __construct(array $iconsRetrievers = [])
    {
        $this->iconsRetrievers = $iconsRetrievers;
    }

    public function getIconsRetriever(?string $codeIdentifier): ?IconsRetrieverInterface
    {
        return $this->iconsRetrievers[$codeIdentifier] ?? null;
    }
}
