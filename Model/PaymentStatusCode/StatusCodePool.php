<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\PaymentStatusCode;

class StatusCodePool
{
    /**
     * @var StatusCodeRetrieverInterface[]
     */
    private $statusCodeRetrievers;

    public function __construct(array $statusCodeRetrievers = [])
    {
        $this->statusCodeRetrievers = $statusCodeRetrievers;
    }

    public function getStatusCodeRetriever(?string $codeIdentifier): ?StatusCodeRetrieverInterface
    {
        return $this->statusCodeRetrievers[$codeIdentifier] ?? null;
    }
}
