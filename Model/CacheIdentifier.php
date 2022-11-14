<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Worldline\PaymentCore\Api\Data\CacheIdentifierInterface;

class CacheIdentifier implements CacheIdentifierInterface
{
    /**
     * @var string
     */
    private $cacheIdentifier = '';

    public function getCacheIdentifier(): string
    {
        return $this->cacheIdentifier;
    }

    public function setCacheIdentifier(string $cacheIdentifier): void
    {
        $this->cacheIdentifier = $cacheIdentifier;
    }
}
